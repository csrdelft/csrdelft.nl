<?php

declare(strict_types=1);
namespace CsrDelft\service\panorama;

use Doctrine\DBAL\Connection;
use DomainException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class PanoramaGameSettings
{
	public function __construct(
		private readonly Connection $connection,
		private readonly PanoramaDailyQuiz $quiz
	) {
	}
	public function get(): array
	{
		$raw = $this->connection->fetchOne(
			'SELECT settings FROM panorama_game_settings WHERE id=1'
		);
		$saved = $raw ? json_decode($raw, true, 32, JSON_THROW_ON_ERROR) : [];
		$games = [];
		foreach (PanoramaArcade::GAMES as $id => $game) {
			$games[$id] = ($saved['games'][$id] ?? []) +
				$game + [
					'enabled' => true,
					'images' =>
						$id === 'spot'
							? PanoramaArcade::PHOTOS
							: array_keys(PanoramaArcade::ART),
				];
		}
		return [
			'revision' => $saved['revision'] ?? 0,
			'games' => $games,
			'uploads' => $saved['uploads'] ?? [],
			'dailyVisibility' => ($saved['dailyVisibility'] ?? []) + [
				'participants' => false,
				'firstTry' => false,
			],
		];
	}
	public function save(string $uid, array $input, ?UploadedFile $photo): array
	{
		$this->connection->beginTransaction();
		$newFile = null;
		try {
			// Eén rij vergrendelt ook gelijktijdige wijzigingen van verschillende commissieleden.
			$this->connection->fetchOne(
				'SELECT id FROM panorama_game_settings WHERE id=1 FOR UPDATE'
			);
			$state = $this->get();
			if ((string) ($input['revision'] ?? '') !== (string) $state['revision']) {
				throw new DomainException(
					'Het beheer is intussen gewijzigd. Vernieuw de pagina.'
				);
			}
			if (($input['game'] ?? '') === 'daily-visibility' && !$photo) {
				$state['dailyVisibility'] = [
					'participants' => ($input['showParticipants'] ?? '') === '1',
					'firstTry' => ($input['showFirstTry'] ?? '') === '1',
				];
			} elseif ($photo) {
				if (count($state['uploads']) >= 100) {
					throw new DomainException('Er zijn al 100 geüploade foto’s.');
				}
				$credit = trim((string) ($input['credit'] ?? ''));
				if ($credit === '' || mb_strlen($credit) > 255) {
					throw new DomainException(
						'Vul fotograaf of bron in (maximaal 255 tekens).'
					);
				}
				$newFile = $this->quiz->storeImage($photo);
				$state['uploads']['u_' . substr($newFile, 0, -5)] = [
					'credit' => $credit,
				];
			} else {
				$kind = $input['game'] ?? null;
				if (!is_string($kind) || !isset($state['games'][$kind])) {
					throw new DomainException('Kies een spel.');
				}
				$name = trim((string) ($input['name'] ?? ''));
				if ($name === '' || mb_strlen($name) > 60) {
					throw new DomainException(
						'Gebruik een spelnaam van maximaal 60 tekens.'
					);
				}
				$images = $input['images'] ?? [];
				if (
					!is_array($images) ||
					!array_is_list($images) ||
					count($images) > 112
				) {
					throw new DomainException('Ongeldige afbeeldingskeuze.');
				}
				$allowed = array_merge(
					array_keys(PanoramaArcade::ART),
					PanoramaArcade::PHOTOS,
					array_keys($state['uploads'])
				);
				foreach ($images as $id) {
					if (!is_string($id) || !in_array($id, $allowed, true)) {
						throw new DomainException('Onbekende afbeelding.');
					}
				}
				$images = array_values(array_unique($images));
				if ($kind === 'spot') {
					foreach ($images as $id) {
						if (isset(PanoramaArcade::ART[$id])) {
							throw new DomainException(
								'Kies echte foto’s voor de verrekijker.'
							);
						}
					}
				}
				$minimum = $kind === 'memory' ? 10 : ($kind === 'spot' ? 4 : 1);
				if (count($images) < $minimum) {
					throw new DomainException(
						'Kies minstens ' . $minimum . ' verschillende afbeeldingen.'
					);
				}
				$changedImages = $images !== $state['games'][$kind]['images'];
				$state['games'][$kind]['name'] = $name;
				$state['games'][$kind]['enabled'] = ($input['enabled'] ?? '') === '1';
				$state['games'][$kind]['images'] = $images;
				if (
					$changedImages &&
					in_array($kind, ['puzzle', 'memory', 'spot'], true)
				) {
					$this->retire($kind);
				}
			}
			$state['revision']++;
			$this->connection->update(
				'panorama_game_settings',
				[
					'settings' => json_encode($state, JSON_THROW_ON_ERROR),
					'updated_by' => $uid,
				],
				['id' => 1]
			);
			$this->connection->commit();
			return $state;
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			if ($newFile) {
				$path = $this->quiz->storedImage($newFile);
				if ($path) {
					unlink($path);
				}
			}
			throw $e;
		}
	}
	private function retire(string $kind): void
	{
		// Andere beelden veranderen de uitdaging: bewaar oude records apart.
		foreach (
			$this->connection->fetchAllAssociative(
				'SELECT uid,state FROM panorama_players FOR UPDATE'
			)
			as $row
		) {
			$s = json_decode($row['state'], true, 64, JSON_THROW_ON_ERROR);
			if (($s['version'] ?? 0) !== 2) {
				continue;
			}
			if (isset($s['records'][$kind])) {
				$s['retiredRecords']['settings'][] = [
					'game' => $kind,
					'record' => $s['records'][$kind],
				];
				unset($s['records'][$kind]);
			}
			if (($s['active']['kind'] ?? '') === $kind) {
				$s['active'] = null;
			}
			if (($s['lastResult']['game'] ?? '') === $kind) {
				unset($s['lastResult']);
			}
			$s['revision']++;
			$this->connection->update(
				'panorama_players',
				[
					'state' => json_encode($s, JSON_THROW_ON_ERROR),
					'revision' => $s['revision'],
					'score' => array_sum(array_column($s['records'], 'best')),
					'stage' => count($s['records']),
				],
				['uid' => $row['uid']]
			);
		}
	}
	public function photo(string $id): ?string
	{
		if (!preg_match('/^[a-f0-9]{40}$/D', $id)) {
			return null;
		}
		if (!isset($this->get()['uploads']['u_' . $id])) {
			return null;
		}
		return $this->quiz->storedImage($id . '.webp');
	}
}
