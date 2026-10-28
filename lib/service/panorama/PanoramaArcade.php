<?php

declare(strict_types=1);
namespace CsrDelft\service\panorama;
use DomainException;

final class PanoramaArcade
{
	public const GAMES = [
		'route' => ['name' => 'Bergroute', 'art' => 'kloof'],
		'puzzle' => ['name' => 'Gebroken uitzicht', 'art' => 'meer'],
		'memory' => ['name' => 'Dubbel zien', 'art' => 'bos'],
		'spot' => ['name' => 'De verrekijker', 'art' => 'duinen'],
		'stars' => ['name' => 'Sterrenpad', 'art' => 'nacht'],
	];
	public const ART = [
		'bos' => 'Landschap',
		'meer' => 'Landschap',
		'nacht' => 'Landschap',
		'kloof' => 'Landschap',
		'sneeuw' => 'Landschap',
		'top' => 'Landschap',
		'heide' => 'Landschap',
		'waterval' => 'Landschap',
		'duinen' => 'Landschap',
		'vulkaan' => 'Landschap',
		'noorderlicht' => 'Landschap',
		'archipel' => 'Landschap',
	];
	public const PHOTOS = ['f01', 'f02', 'f03', 'f04', 'f05', 'f06', 'f07'];
	public const STAR_STEP = 0.34;
	public static function initial(): array
	{
		return [
			'version' => 2,
			'revision' => 0,
			'records' => [],
			'medals' => [],
			'plays' => 0,
			'active' => null,
		];
	}
	private static function random(string $seed, string $key, int $max): int
	{
		return (int) hexdec(substr(hash('sha256', $seed . ':' . $key), 0, 7)) %
			$max;
	}
	private static function shuffle(array $items, string $seed): array
	{
		for ($i = count($items) - 1; $i > 0; $i--) {
			$j = self::random($seed, (string) $i, $i + 1);
			[$items[$i], $items[$j]] = [$items[$j], $items[$i]];
		}
		return $items;
	}
	public static function begin(
		string $kind,
		int $level,
		?string $seed = null,
		?array $settings = null
	): array {
		if (!isset(self::GAMES[$kind]) || $level < 1 || $level > 3) {
			throw new DomainException(
				'Kies een spel en een moeilijkheid van 1 tot 3.'
			);
		}
		// Eén wedstrijdbaan per spel: snelheidstrofeeën vergelijken dezelfde uitdaging.
		$seed ??=
			$level === 3 ? 'panorama-arcade-v2:' . $kind : bin2hex(random_bytes(16));
		$art = $settings['images'] ?? array_keys(self::ART);
		$a = [
			'id' => bin2hex(random_bytes(12)),
			'kind' => $kind,
			'level' => $level,
			'started' => microtime(true),
			'mistakes' => 0,
			'moves' => 0,
			'seed' => $seed,
			'photos' => $settings['images'] ?? self::PHOTOS,
			'art' => $art[self::random($seed, 'art', count($art))],
		];
		switch ($kind) {
			case 'route':
				$a['puzzle'] = PanoramaRoute::create($seed, $level);
				break;
			case 'puzzle':
				$n = 2 + $level;
				$a['size'] = $n;
				$a['tiles'] = self::shuffle(range(0, $n * $n - 1), $seed);
				if ($a['tiles'] === range(0, $n * $n - 1)) {
					[$a['tiles'][0], $a['tiles'][1]] = [$a['tiles'][1], $a['tiles'][0]];
				}
				$a['minimum'] = self::minimumSwaps($a['tiles']);
				break;
			case 'memory':
				$pairs = 4 + 2 * $level;
				$a['deck'] = self::shuffle(
					array_merge(
						array_slice(self::shuffle($art, $seed . 'art'), 0, $pairs),
						array_slice(self::shuffle($art, $seed . 'art'), 0, $pairs)
					),
					$seed
				);
				$a['matched'] = [];
				$a['open'] = [];
				break;
			case 'spot':
				$a['round'] = 0;
				$a['rounds'] = 3 + 2 * $level;
				$a['answers'] = [];
				break;
			case 'stars':
				$a['round'] = 0;
				$a['rounds'] = 2 + $level;
				$a['phase'] = 'idle';
				$a['position'] = 0;
				break;
		}
		$a['started'] = microtime(true);
		return $a;
	}
	public static function validRoute(array $p, array $path): bool
	{
		return PanoramaRoute::evaluate($p, $path) !== null;
	}
	private static function minimumSwaps(array $tiles): int
	{
		$seen = [];
		$cycles = 0;
		foreach (array_keys($tiles) as $i) {
			if (isset($seen[$i])) {
				continue;
			}
			$cycles++;
			for ($j = $i; !isset($seen[$j]); $j = $tiles[$j]) {
				$seen[$j] = true;
			}
		}
		return count($tiles) - $cycles;
	}
	private static function question(array $a): array
	{
		$arts = $a['photos'] ?? self::PHOTOS;
		$answer = self::shuffle($arts, $a['seed'] . ':round' . $a['round'])[0];
		$others = array_values(
			array_diff(self::shuffle($arts, $a['seed'] . ':options' . $a['round']), [
				$answer,
			])
		);
		return [
			'answer' => $answer,
			'options' => self::shuffle(
				array_merge([$answer], array_slice($others, 0, 3)),
				$a['seed'] . ':order' . $a['round']
			),
		];
	}
	private static function sequence(array $a): array
	{
		$n = 2 + $a['level'] + $a['round'];
		$seq = [];
		for ($i = 0; $i < $n; $i++) {
			$seq[] = self::random($a['seed'], 'star' . $a['round'] . ':' . $i, 9);
		}
		return $seq;
	}
	public static function view(array $a): array
	{
		$v = $a;
		unset($v['seed'], $v['started'], $v['deck'], $v['minimum'], $v['photos']);
		$v['elapsed'] = max(0, (int) ((microtime(true) - $a['started']) * 1000));
		if ($a['kind'] === 'route') {
			unset($v['puzzle']['solution'], $v['puzzle']['optimal']);
		}
		if ($a['kind'] === 'memory') {
			$v['cards'] = array_map(
				fn($i) => in_array($i, $a['matched'], true) ||
				in_array($i, $a['open'], true)
					? $a['deck'][$i]
					: null,
				range(0, count($a['deck']) - 1)
			);
		}
		if ($a['kind'] === 'spot') {
			$q = self::question($a);
			$v['crop'] = $q['answer'];
			$v['options'] = $q['options'];
			$regions = self::shuffle(
				[
					[12, 12],
					[50, 12],
					[88, 12],
					[12, 50],
					[50, 50],
					[88, 50],
					[12, 88],
					[50, 88],
					[88, 88],
				],
				$a['seed'] . ':crop-regions'
			);
			$region = $regions[$a['round'] % count($regions)];
			$v['cropPosition'] = [
				$region[0] + self::random($a['seed'], 'x' . $a['round'], 15) - 7,
				$region[1] + self::random($a['seed'], 'y' . $a['round'], 15) - 7,
			];
		}
		if ($a['kind'] === 'stars') {
			if ($a['phase'] === 'watch' && microtime(true) >= $a['until']) {
				$v['phase'] = 'input';
			}
			$v['sequence'] = $v['phase'] === 'watch' ? self::sequence($a) : null;
			$v['length'] = count(self::sequence($a));
			$v['waitMs'] = max(
				0,
				(int) (1000 * (($a['until'] ?? 0) - microtime(true)))
			);
		}
		return $v;
	}
	/** Retourneert de nieuwe poging en of deze voltooid is. Geen scores uit de browser. */
	public static function move(array $a, string $move, array $input): array
	{
		$done = false;
		switch ($a['kind']) {
			case 'route':
				if (
					$move !== 'route' ||
					!isset($input['path']) ||
					!is_array($input['path']) ||
					!self::validRoute($a['puzzle'], $input['path'])
				) {
					$a['mistakes']++;
					break;
				}
				$a['routeResult'] = PanoramaRoute::evaluate(
					$a['puzzle'],
					$input['path']
				);
				$done = true;
				break;
			case 'puzzle':
				if ($move === 'swaps') {
					$ops = $input['ops'] ?? null;
					if (
						!is_array($ops) ||
						!array_is_list($ops) ||
						count($ops) < 1 ||
						count($ops) > 128
					) {
						throw new DomainException('Ongeldige wissels.');
					}
					foreach ($ops as $op) {
						if (!is_array($op) || !array_is_list($op) || count($op) !== 2) {
							throw new DomainException('Ongeldige wissel.');
						}
						[$a, $done] = self::move($a, 'swap', [
							'a' => $op[0],
							'b' => $op[1],
						]);
						if ($done) {
							break;
						}
					}
					break;
				}
				if ($move !== 'swap') {
					throw new DomainException('Kies twee stukken.');
				}
				$i = self::index($input, 'a', count($a['tiles']));
				$j = self::index($input, 'b', count($a['tiles']));
				if ($i === $j) {
					throw new DomainException('Kies twee verschillende stukken.');
				}
				[$a['tiles'][$i], $a['tiles'][$j]] = [$a['tiles'][$j], $a['tiles'][$i]];
				$a['moves']++;
				$done = $a['tiles'] === range(0, count($a['tiles']) - 1);
				break;
			case 'memory':
				if ($move !== 'flip') {
					throw new DomainException('Draai een kaart om.');
				}
				$i = self::index($input, 'index', count($a['deck']));
				if (count($a['open']) === 2) {
					$a['open'] = [];
				}
				if (
					in_array($i, $a['matched'], true) ||
					in_array($i, $a['open'], true)
				) {
					throw new DomainException('Deze kaart ligt al open.');
				}
				$a['open'][] = $i;
				if (count($a['open']) === 2) {
					$a['moves']++;
					if ($a['deck'][$a['open'][0]] === $a['deck'][$a['open'][1]]) {
						$a['matched'] = array_merge($a['matched'], $a['open']);
					} else {
						$a['mistakes']++;
					}
				}
				$done = count($a['matched']) === count($a['deck']);
				break;
			case 'spot':
				$q = self::question($a);
				$answer = $input['answer'] ?? null;
				if (
					$move !== 'answer' ||
					!is_string($answer) ||
					!in_array($answer, $q['options'], true)
				) {
					throw new DomainException('Kies een van de vier uitzichten.');
				}
				$correct = $answer === $q['answer'];
				$a['answers'][] = $correct;
				if (!$correct) {
					$a['mistakes']++;
				}
				$a['round']++;
				$done = $a['round'] === $a['rounds'];
				break;
			case 'stars':
				if ($move === 'reveal') {
					if ($a['phase'] !== 'idle') {
						$a['mistakes']++;
					}
					$a['phase'] = 'watch';
					$a['position'] = 0;
					$a['until'] =
						microtime(true) +
						count(self::sequence($a)) * self::STAR_STEP +
						0.35;
					break;
				}
				if (
					$move !== 'pattern' ||
					$a['phase'] === 'idle' ||
					microtime(true) < ($a['until'] ?? PHP_FLOAT_MAX)
				) {
					throw new DomainException('Kijk eerst naar het patroon.');
				}
				$pattern = $input['pattern'] ?? null;
				$seq = self::sequence($a);
				if (
					!is_array($pattern) ||
					!array_is_list($pattern) ||
					count($pattern) > count($seq)
				) {
					throw new DomainException('Ongeldig patroon.');
				}
				foreach ($pattern as $i => $star) {
					if (!is_int($star) || $star < 0 || $star > 8) {
						throw new DomainException('Ongeldige ster.');
					}
					if ($seq[$i] !== $star) {
						$a['mistakes']++;
						$a['phase'] = 'idle';
						$a['position'] = 0;
						break 2;
					}
				}
				if (count($pattern) !== count($seq)) {
					throw new DomainException('Maak het patroon af.');
				}
				$a['round']++;
				$a['position'] = 0;
				$a['phase'] = 'idle';
				$done = $a['round'] === $a['rounds'];
				break;
		}
		return [$a, $done];
	}
	private static function index(array $input, string $key, int $limit): int
	{
		if (
			!isset($input[$key]) ||
			!is_int($input[$key]) ||
			$input[$key] < 0 ||
			$input[$key] >= $limit
		) {
			throw new DomainException('Dit vakje bestaat niet.');
		}
		return $input[$key];
	}
	public static function result(array $a): array
	{
		$ms = max(1, (int) ((microtime(true) - $a['started']) * 1000));
		$base = [1 => 900, 2 => 1400, 3 => 2000][$a['level']];
		if ($a['kind'] === 'spot') {
			$base = (int) floor(
				($base * min($a['rounds'], count(array_filter($a['answers'])))) /
					$a['rounds']
			);
		}
		$extra = $a['kind'] === 'puzzle' ? max(0, $a['moves'] - $a['minimum']) : 0;
		$score = max(
			100,
			$base -
				$a['mistakes'] * 90 -
				$extra * 18 -
				(int) floor($ms / 1000) * 2 -
				($a['kind'] === 'route'
					? max(0, $a['routeResult']['objective'] - $a['puzzle']['optimal']) *
						35
					: 0)
		);
		return [
			'game' => $a['kind'],
			'level' => $a['level'],
			'score' => $score,
			'time' => $ms,
			'mistakes' => $a['mistakes'],
			'moves' => $a['moves'],
			'art' => $a['art'],
			'routeResult' => $a['routeResult'] ?? null,
			'perfect' =>
				$a['mistakes'] === 0 &&
				$extra === 0 &&
				($a['kind'] !== 'route' ||
					$a['routeResult']['objective'] === $a['puzzle']['optimal']),
			'medal' =>
				$score >= 1600
					? 'gold'
					: ($score >= 1100
						? 'silver'
						: ($score >= 600
							? 'bronze'
							: null)),
		];
	}
	public static function record(array $state, array $result): array
	{
		$kind = $result['game'];
		$record = $state['records'][$kind] ?? [
			'best' => 0,
			'plays' => 0,
			'fastestHard' => null,
		];
		$record['plays']++;
		$record['best'] = max($record['best'], $result['score']);
		if ($result['level'] === 3 && $result['perfect']) {
			$record['fastestHard'] =
				$record['fastestHard'] === null
					? $result['time']
					: min($record['fastestHard'], $result['time']);
		}
		$state['records'][$kind] = $record;
		$state['plays']++;
		$state['active'] = null;
		$state['lastResult'] = $result;
		$awards = ['eerste-uitzicht'];
		if (count($state['records']) === 5) {
			$awards[] = 'vijfkamper';
		}
		if ($result['perfect']) {
			$awards[] = 'foutloos';
		}
		if (
			$result['level'] >= 2 &&
			$result['time'] <= 30000 &&
			$result['perfect']
		) {
			$awards[] = 'snelle-blik';
		}
		if ($result['medal']) {
			$awards[] = $kind . '-' . $result['medal'];
		}
		if ($state['plays'] >= 10) {
			$awards[] = 'vaste-gast';
		}
		if (
			count(array_filter($state['records'], fn($r) => $r['best'] >= 1600)) === 5
		) {
			$awards[] = 'panorama-meester';
		}
		$state['medals'] = array_values(
			array_unique(array_merge($state['medals'], $awards))
		);
		return $state;
	}
}
