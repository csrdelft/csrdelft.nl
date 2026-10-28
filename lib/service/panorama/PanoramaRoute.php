<?php

declare(strict_types=1);
namespace CsrDelft\service\panorama;

final class PanoramaRoute
{
	private static function random(string $seed, string $key, int $max): int
	{
		return (int) hexdec(substr(hash('sha256', $seed . ':' . $key), 0, 7)) %
			$max;
	}
	public static function create(string $seed, int $level): array
	{
		$n = $level === 3 ? 5 : 4;
		$nodes = [];
		$edges = [];
		$start = ($n - 1) * $n;
		$goal = $n - 1;
		// Eén herkenbare opdracht, zonder voorraden of sloten.
		$targets = $level === 1 ? [$n + 1] : [$n + 1, $n * $n - $n - 2];
		$springs = [];
		$keyNode = -1;
		$bonuses = [$n * 2 - 1, ($n - 2) * $n];
		for ($i = 0; $i < $n * $n; $i++) {
			$nodes[] = [
				'x' => 10 + (($i % $n) * 80) / ($n - 1),
				'y' => 10 + (intdiv($i, $n) * 80) / ($n - 1),
				'target' => in_array($i, $targets, true),
				'bonus' => in_array($i, $bonuses, true),
				'spring' => in_array($i, $springs, true),
				'key' => $i === $keyNode,
			];
		}
		for ($i = 0; $i < $n * $n; $i++) {
			foreach ([1, $n] as $d) {
				$j = $i + $d;
				if ($j >= $n * $n || ($d === 1 && intdiv($i, $n) !== intdiv($j, $n))) {
					continue;
				}
				$edges[] = [
					'a' => $i,
					'b' => $j,
					'cost' => 1 + self::random($seed, 'cost' . $i . ':' . $j, 3),
					'water' => 0,
					'locked' => false,
				];
			}
		}
		for ($y = 0; $y < $n - 1; $y++) {
			for ($x = 0; $x < $n - 1; $x++) {
				if (self::random($seed, 'bridge' . $x . $y, 5) !== 0) {
					continue;
				}
				$a = $y * $n + $x;
				$b = $a + $n + 1;
				$edges[] = [
					'a' => $a,
					'b' => $b,
					'cost' => 1 + self::random($seed, 'shortcut' . $a, 3),
					'water' => 0,
					'locked' => false,
				];
			}
		}
		$p = [
			'size' => $n,
			'nodes' => $nodes,
			'edges' => $edges,
			'start' => $start,
			'goal' => $goal,
			'targets' => $targets,
			'bonuses' => $bonuses,
			'springs' => $springs,
			'keyNode' => $keyNode,
			'water' => 0,
			'budget' => 100 + $level * 25,
			'maxSteps' => 70,
		];
		$best = self::solve($p);
		$p['solution'] = $best['path'];
		$p['optimal'] = $best['objective'];
		return $p;
	}
	private static function arrive(array $p, array $s, int $node): array
	{
		$s[0] = $node;
		$i = array_search($node, $p['targets'], true);
		if ($i !== false) {
			$s[1] |= 1 << $i;
		}
		$i = array_search($node, $p['bonuses'], true);
		if ($i !== false) {
			$s[2] |= 1 << $i;
		}
		$i = array_search($node, $p['springs'], true);
		if ($i !== false && ($s[4] & (1 << $i)) === 0) {
			$s[3] = min(6, $s[3] + 2);
			$s[4] |= 1 << $i;
		}
		if ($node === $p['keyNode']) {
			$s[5] = 1;
		}
		return $s;
	}
	private static function coinCount(int $mask): int
	{
		return substr_count(decbin($mask), '1');
	}
	private static function adjacency(array $p): array
	{
		$list = array_fill(0, count($p['nodes']), []);
		foreach ($p['edges'] as $e) {
			$list[$e['a']][] = $e + ['to' => $e['b']];
			$list[$e['b']][] = $e + ['to' => $e['a']];
		}
		return $list;
	}
	public static function solve(array $p): array
	{
		$adj = self::adjacency($p);
		$initial = self::arrive(
			$p,
			[$p['start'], 0, 0, $p['water'], 0, 0],
			$p['start']
		);
		$first = implode(':', $initial);
		$dist = [$first => 0];
		$parent = [];
		$queue = new \SplPriorityQueue();
		$queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);
		$queue->insert([$first, 0], 0);
		$best = null;
		$bestObjective = PHP_INT_MAX;
		$all = (1 << count($p['targets'])) - 1;
		while (!$queue->isEmpty()) {
			[$id, $cost] = $queue->extract();
			if (($dist[$id] ?? PHP_INT_MAX) !== $cost) {
				continue;
			}
			$s = array_map('intval', explode(':', $id));
			if ($s[0] === $p['goal'] && $s[1] === $all) {
				$objective = $cost - 4 * self::coinCount($s[2]);
				if ($objective < $bestObjective) {
					$bestObjective = $objective;
					$best = $id;
				}
			}
			foreach ($adj[$s[0]] as $e) {
				if ($e['water'] > $s[3] || ($e['locked'] && !$s[5])) {
					continue;
				}
				$nextCost = $cost + $e['cost'];
				if ($nextCost > $p['budget'] || $nextCost - 12 > $bestObjective) {
					continue;
				}
				$next = $s;
				$next[3] -= $e['water'];
				$next = self::arrive($p, $next, $e['to']);
				$nextId = implode(':', $next);
				if ($nextCost >= ($dist[$nextId] ?? PHP_INT_MAX)) {
					continue;
				}
				$dist[$nextId] = $nextCost;
				$parent[$nextId] = $id;
				$queue->insert([$nextId, $nextCost], -$nextCost);
			}
		}
		if ($best === null) {
			throw new \RuntimeException('Geen geldige route gegenereerd.');
		}
		$path = [];
		for ($id = $best; ; $id = $parent[$id]) {
			$path[] = (int) explode(':', $id)[0];
			if ($id === $first) {
				break;
			}
		}
		return ['path' => array_reverse($path), 'objective' => $bestObjective];
	}
	public static function evaluate(array $p, array $path): ?array
	{
		if (
			!array_is_list($path) ||
			count($path) < 2 ||
			count($path) > $p['maxSteps'] ||
			$path[0] !== $p['start'] ||
			end($path) !== $p['goal']
		) {
			return null;
		}
		$s = self::arrive($p, [$p['start'], 0, 0, $p['water'], 0, 0], $p['start']);
		$cost = 0;
		$adj = self::adjacency($p);
		foreach (array_slice($path, 1) as $node) {
			if (!is_int($node) || !isset($p['nodes'][$node])) {
				return null;
			}
			$edge = null;
			foreach ($adj[$s[0]] as $e) {
				if ($e['to'] === $node) {
					$edge = $e;
					break;
				}
			}
			if (!$edge || $edge['water'] > $s[3] || ($edge['locked'] && !$s[5])) {
				return null;
			}
			$cost += $edge['cost'];
			if ($cost > $p['budget']) {
				return null;
			}
			$s[3] -= $edge['water'];
			$s = self::arrive($p, $s, $node);
		}
		if ($s[1] !== (1 << count($p['targets'])) - 1) {
			return null;
		}
		$coins = self::coinCount($s[2]);
		return [
			'cost' => $cost,
			'water' => $s[3],
			'coins' => $coins,
			'objective' => $cost - 4 * $coins,
		];
	}
}
