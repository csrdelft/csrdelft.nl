<?php

declare(strict_types=1);
namespace CsrDelft\tests\service\panorama;
use CsrDelft\service\panorama\PanoramaArcade;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PanoramaArcadeTest extends TestCase
{
	public static function complete(array $a): array
	{
		$done = false;
		switch ($a['kind']) {
			case 'route':
				[$a, $done] = PanoramaArcade::move($a, 'route', [
					'path' => $a['puzzle']['solution'],
				]);
				break;
			case 'puzzle':
				for ($i = 0; $i < count($a['tiles']); $i++) {
					if ($a['tiles'][$i] === $i) {
						continue;
					}
					[$a, $done] = PanoramaArcade::move($a, 'swap', [
						'a' => $i,
						'b' => array_search($i, $a['tiles'], true),
					]);
				}
				break;
			case 'memory':
				$pairs = [];
				foreach ($a['deck'] as $i => $art) {
					$pairs[$art][] = $i;
				}
				foreach ($pairs as $pair) {
					foreach ($pair as $i) {
						[$a, $done] = PanoramaArcade::move($a, 'flip', ['index' => $i]);
					}
				}
				break;
			case 'spot':
				while (!$done) {
					$view = PanoramaArcade::view($a);
					[$a, $done] = PanoramaArcade::move($a, 'answer', [
						'answer' => $view['crop'],
					]);
				}
				break;
			case 'stars':
				while (!$done) {
					[$a] = PanoramaArcade::move($a, 'reveal', []);
					$sequence = PanoramaArcade::view($a)['sequence'];
					$a['until'] = microtime(true) - 1;
					[$a, $done] = PanoramaArcade::move($a, 'pattern', [
						'pattern' => $sequence,
					]);
				}
				break;
		}
		self::assertTrue($done);
		return $a;
	}
	public function testAllFiveGamesWorkAtAllThreeLevels(): void
	{
		foreach (array_keys(PanoramaArcade::GAMES) as $kind) {
			foreach ([1, 2, 3] as $level) {
				$a = PanoramaArcade::begin($kind, $level, 'test-' . $kind . $level);
				$a = self::complete($a);
				$r = PanoramaArcade::result($a);
				self::assertSame(0, $r['mistakes']);
				self::assertTrue($r['perfect']);
				self::assertSame([1 => 900, 2 => 1400, 3 => 2000][$level], $r['score']);
			}
		}
	}
	public function testRoutePlansAreSolvableAndRejectInvalidRoutes(): void
	{
		foreach ([1, 2, 3] as $level) {
			for ($i = 0; $i < 5; $i++) {
				$p = \CsrDelft\service\panorama\PanoramaRoute::create(
					'test-route' . $level . ':' . $i,
					$level
				);
				$result = \CsrDelft\service\panorama\PanoramaRoute::evaluate(
					$p,
					$p['solution']
				);
				self::assertNotNull($result);
				self::assertSame($p['optimal'], $result['objective']);
				self::assertFalse(
					PanoramaArcade::validRoute($p, [$p['start'], $p['goal']])
				);
				self::assertFalse(
					PanoramaArcade::validRoute($p, array_map('strval', $p['solution']))
				);
				self::assertFalse(
					PanoramaArcade::validRoute($p, [$p['start'], [], $p['goal']])
				);
				foreach ($p['bonuses'] as $node) {
					self::assertNotContains($node, $p['targets']);
					self::assertNotContains($node, $p['springs']);
				}
			}
		}
	}
	public function testHiddenMemoryCardsAndRouteSolutionAreNotPublished(): void
	{
		$a = PanoramaArcade::begin('memory', 3, 'hidden');
		$v = PanoramaArcade::view($a);
		self::assertArrayNotHasKey('deck', $v);
		self::assertArrayNotHasKey('seed', $v);
		self::assertSame(array_fill(0, 20, null), $v['cards']);
		$v = PanoramaArcade::view(PanoramaArcade::begin('route', 3));
		self::assertArrayNotHasKey('solution', $v['puzzle']);
	}
	public function testOnlyBestScoresCountAndAwardsAreNotDuplicated(): void
	{
		$s = PanoramaArcade::initial();
		foreach (array_keys(PanoramaArcade::GAMES) as $kind) {
			$a = self::complete(PanoramaArcade::begin($kind, 3, 'awards' . $kind));
			$s = PanoramaArcade::record($s, PanoramaArcade::result($a));
		}
		self::assertSame(10000, array_sum(array_column($s['records'], 'best')));
		self::assertContains('vijfkamper', $s['medals']);
		self::assertContains('panorama-meester', $s['medals']);
		$r = PanoramaArcade::result(
			self::complete(PanoramaArcade::begin('route', 1))
		);
		$s = PanoramaArcade::record($s, $r);
		self::assertSame(10000, array_sum(array_column($s['records'], 'best')));
		self::assertSame(count($s['medals']), count(array_unique($s['medals'])));
	}
	public function testExtraSwapsAndWrongAnswersCostPoints(): void
	{
		$a = PanoramaArcade::begin('puzzle', 3, 'cost');
		[$a] = PanoramaArcade::move($a, 'swap', ['a' => 0, 'b' => 1]);
		[$a] = PanoramaArcade::move($a, 'swap', ['a' => 0, 'b' => 1]);
		$a = self::complete($a);
		$result = PanoramaArcade::result($a);
		self::assertLessThan(2000, $result['score']);
		self::assertFalse($result['perfect']);
		$s = PanoramaArcade::record(PanoramaArcade::initial(), $result);
		self::assertNull($s['records']['puzzle']['fastestHard']);
		$a = PanoramaArcade::begin('spot', 1);
		$v = PanoramaArcade::view($a);
		$wrong = array_values(array_diff($v['options'], [$v['crop']]))[0];
		[$a, $done] = PanoramaArcade::move($a, 'answer', ['answer' => $wrong]);
		self::assertSame(1, $a['mistakes']);
		self::assertSame(1, $a['round']);
		self::assertFalse($done);
	}
	public function testStarsCannotBeEnteredBeforePlaybackHasFinished(): void
	{
		$a = PanoramaArcade::begin('stars', 2);
		[$a] = PanoramaArcade::move($a, 'reveal', []);
		$sequence = PanoramaArcade::view($a)['sequence'];
		$this->expectException(DomainException::class);
		PanoramaArcade::move($a, 'pattern', ['pattern' => $sequence]);
	}
	public function testTimeCannotCreateNegativeScores(): void
	{
		$a = self::complete(PanoramaArcade::begin('route', 1));
		$a['started'] = microtime(true) - 10000;
		self::assertSame(100, PanoramaArcade::result($a)['score']);
	}
	public function testWrongGuessesDoNotEarnMedalsOrSpeedAwards(): void
	{
		$a = PanoramaArcade::begin('spot', 3);
		$done = false;
		while (!$done) {
			$v = PanoramaArcade::view($a);
			$wrong = array_values(array_diff($v['options'], [$v['crop']]))[0];
			[$a, $done] = PanoramaArcade::move($a, 'answer', ['answer' => $wrong]);
		}
		$result = PanoramaArcade::result($a);
		self::assertSame(100, $result['score']);
		self::assertNull($result['medal']);
		$state = PanoramaArcade::record(PanoramaArcade::initial(), $result);
		self::assertNotContains('snelle-blik', $state['medals']);
		self::assertNull($state['records']['spot']['fastestHard']);
	}
	public function testCompetitionCoursesAreTheSameForAllPlayers(): void
	{
		foreach (array_keys(PanoramaArcade::GAMES) as $kind) {
			$a = PanoramaArcade::begin($kind, 3);
			$b = PanoramaArcade::begin($kind, 3);
			self::assertSame($a['seed'], $b['seed']);
			self::assertNotSame($a['id'], $b['id']);
		}
	}

	public function testViewfinderUsesAllNinePhotoRegions(): void
	{
		for ($seed = 0; $seed < 20; $seed++) {
			$a = PanoramaArcade::begin('spot', 3, 'crop-test-' . $seed);
			$regions = [];
			for ($round = 0; $round < 9; $round++) {
				$a['round'] = $round;
				$view = PanoramaArcade::view($a);
				$position = $view['cropPosition'];
				self::assertSame($position, PanoramaArcade::view($a)['cropPosition']);
				foreach ($position as $coordinate) {
					self::assertGreaterThanOrEqual(5, $coordinate);
					self::assertLessThanOrEqual(95, $coordinate);
				}
				$col = $position[0] < 30 ? 0 : ($position[0] > 70 ? 2 : 1);
				$row = $position[1] < 30 ? 0 : ($position[1] > 70 ? 2 : 1);
				$regions[$row * 3 + $col] = true;
			}
			self::assertCount(9, $regions);
		}
	}
}
