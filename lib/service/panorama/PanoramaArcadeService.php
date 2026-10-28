<?php

declare(strict_types=1);
namespace CsrDelft\service\panorama;
use Doctrine\DBAL\Connection;
use DomainException;

final class PanoramaArcadeService
{
	public function __construct(
		private readonly Connection $connection,
		private readonly ?PanoramaGameSettings $settings = null
	) {
	}
	private function row(string $uid, bool $lock = false): ?array
	{
		$row = $this->connection->fetchAssociative(
			'SELECT * FROM panorama_players WHERE uid=?' .
				($lock ? ' FOR UPDATE' : ''),
			[$uid]
		);
		return $row ?: null;
	}
	private function state(array $row): array
	{
		return json_decode($row['state'], true, 64, JSON_THROW_ON_ERROR);
	}
	public function snapshot(string $uid, bool $light = false): array
	{
		$row = $this->row($uid);
		$state = $row ? $this->state($row) : null;
		if (($state['version'] ?? 0) !== 2) {
			$state = null;
		}
		$result = [
			'games' => $this->settings
				? $this->settings->get()['games']
				: PanoramaArcade::GAMES,
			'art' => PanoramaArcade::ART,
			'photos' => $this->settings
				? array_merge(
					PanoramaArcade::PHOTOS,
					array_keys($this->settings->get()['uploads'])
				)
				: PanoramaArcade::PHOTOS,
			'uid' => $uid,
			'name' => $row['display_name'] ?? null,
			'state' => null,
			'leaderboard' => $light
				? []
				: $this->connection->fetchAllAssociative(
					"SELECT u.uid, COALESCE(p.display_name,q.name) AS name, COALESCE(p.score,0)+COALESCE(q.points,0) AS score, COALESCE(p.stage,0) AS completed, COALESCE(q.points,0) AS quiz_points FROM (SELECT uid FROM panorama_players WHERE JSON_EXTRACT(state,'$.version')=2 UNION SELECT uid FROM panorama_daily_answers) u LEFT JOIN panorama_players p ON p.uid=u.uid AND JSON_EXTRACT(p.state,'$.version')=2 LEFT JOIN (SELECT uid,MAX(display_name) AS name,SUM(points) AS points,MAX(CASE WHEN points>0 THEN answered_at ELSE NULL END) AS last_answer FROM panorama_daily_answers GROUP BY uid) q ON q.uid=u.uid ORDER BY score DESC, completed DESC, GREATEST(COALESCE(p.updated_at,'1970-01-01'),COALESCE(q.last_answer,'1970-01-01')) ASC, u.uid ASC LIMIT 20"
				),
			'champions' => [],
		];
		if ($state) {
			$result['state'] = [
				'revision' => $state['revision'],
				'records' => $state['records'],
				'medals' => $state['medals'],
				'plays' => $state['plays'],
				'total' => array_sum(array_column($state['records'], 'best')),
				'active' => $state['active']
					? PanoramaArcade::view($state['active'])
					: null,
				'lastResult' => $state['lastResult'] ?? null,
			];
		}
		if ($light) {
			unset($result['leaderboard'], $result['champions']);
			$result['partial'] = true;
			return $result;
		}
		foreach (array_keys(PanoramaArcade::GAMES) as $kind) {
			$path = '$.records.' . $kind . '.fastestHard';
			$winner = $this->connection->fetchAssociative(
				"SELECT uid, display_name AS name, CAST(JSON_UNQUOTE(JSON_EXTRACT(state, ?)) AS UNSIGNED) AS time FROM panorama_players WHERE JSON_EXTRACT(state,'$.version')=2 AND JSON_EXTRACT(state, ?) IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(state, ?)) <> 'null' ORDER BY time ASC, uid ASC LIMIT 1",
				[$path, $path, $path]
			);
			if ($winner) {
				$result['champions'][$kind] = $winner;
			}
		}
		return $result;
	}
	public function act(
		string $uid,
		string $name,
		string $action,
		array $input
	): array {
		$this->connection->beginTransaction();
		try {
			$row = $this->row($uid, true);
			$old = $row ? $this->state($row) : null;
			$state = ($old['version'] ?? 0) === 2 ? $old : PanoramaArcade::initial();
			if (($input['revision'] ?? null) !== $state['revision']) {
				throw new DomainException(
					'Je spelstand is veranderd. De nieuwste stand is geladen; probeer opnieuw.'
				);
			}
			$oldTotal = array_sum(array_column($state['records'], 'best'));
			if ($action === 'begin') {
				$kind = $input['game'] ?? null;
				$level = $input['level'] ?? null;
				if (!is_string($kind) || !is_int($level)) {
					throw new DomainException('Kies een spel en een niveau.');
				}
				$config = $this->settings
					? $this->settings->get()['games'][$kind] ?? null
					: null;
				if ($config && !$config['enabled']) {
					throw new DomainException('Dit spel is tijdelijk gesloten.');
				}
				$state['active'] = PanoramaArcade::begin($kind, $level, null, $config);
				unset($state['lastResult']);
			} elseif ($action === 'move') {
				if (
					!$state['active'] ||
					($input['attempt'] ?? null) !== $state['active']['id']
				) {
					throw new DomainException(
						'Deze poging is al afgelopen of vervangen.'
					);
				}
				$move = $input['move'] ?? null;
				if (!is_string($move)) {
					throw new DomainException('Ongeldige zet.');
				}
				[$active, $finished] = PanoramaArcade::move(
					$state['active'],
					$move,
					$input
				);
				$state['active'] = $active;
				if ($finished) {
					$state = PanoramaArcade::record(
						$state,
						PanoramaArcade::result($active)
					);
				}
			} elseif ($action === 'abandon') {
				$state['active'] = null;
				unset($state['lastResult']);
			} else {
				throw new DomainException('Onbekende spelactie.');
			}
			if ($old && ($old['version'] ?? 0) !== 2) {
				$state['previousExpedition'] = $old;
			}
			$state['revision']++;
			$score = array_sum(array_column($state['records'], 'best'));
			$fields = [
				'display_name' => mb_substr($name, 0, 160),
				'state' => json_encode($state, JSON_THROW_ON_ERROR),
				'revision' => $state['revision'],
				'score' => $score,
				'stage' => count($state['records']),
				'updated_at' =>
					$score > $oldTotal || !$row
						? date('Y-m-d H:i:s')
						: $row['updated_at'],
			];
			if ($row) {
				$this->connection->update('panorama_players', $fields, ['uid' => $uid]);
			} else {
				$this->connection->insert(
					'panorama_players',
					['uid' => $uid] + $fields
				);
			}
			$this->connection->commit();
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			throw $e;
		}
		return $this->snapshot($uid, true);
	}
}
