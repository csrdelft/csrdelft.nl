<?php

declare(strict_types=1);
namespace CsrDelft\tests\service\panorama;
use CsrDelft\service\panorama\PanoramaArcade;
use CsrDelft\service\panorama\PanoramaArcadeService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use DomainException;
use PHPUnit\Framework\TestCase;
final class PanoramaArcadeServiceTest extends TestCase
{
	private Connection $db;
	private PanoramaArcadeService $service;
	private string $uid;
	protected function setUp(): void
	{
		$url = getenv('PANORAMA_TEST_DATABASE_URL');
		if (!$url) {
			$this->markTestSkipped(
				'Stel een lokale testdatabase met Panorama-migratie in.'
			);
		}
		$this->db = DriverManager::getConnection(['url' => $url]);
		$this->db->setNestTransactionsWithSavepoints(true);
		$this->db->beginTransaction();
		$this->service = new PanoramaArcadeService($this->db);
		$this->uid = 'arcade_test_' . bin2hex(random_bytes(5));
	}
	protected function tearDown(): void
	{
		if (isset($this->db)) {
			while ($this->db->getTransactionNestingLevel() > 0) {
				$this->db->rollBack();
			}
			$this->db->close();
		}
	}
	private function act(string $action, array $values = []): array
	{
		$snapshot = $this->service->snapshot($this->uid);
		$this->service->act(
			$this->uid,
			'Arcadetest',
			$action,
			['revision' => $snapshot['state']['revision'] ?? 0] + $values
		);
		return $this->service->snapshot($this->uid);
	}
	private function raw(): array
	{
		return json_decode(
			$this->db->fetchOne('SELECT state FROM panorama_players WHERE uid=?', [
				$this->uid,
			]),
			true
		);
	}
	private function finishRoute(int $level = 3): array
	{
		$this->act('begin', ['game' => 'route', 'level' => $level]);
		$s = $this->raw();
		return $this->act('move', [
			'attempt' => $s['active']['id'],
			'move' => 'route',
			'path' => $s['active']['puzzle']['solution'],
			'score' => 999999,
		]);
	}
	public function testScoresAreServerOwnedAndOnlyBestCounts(): void
	{
		$g = $this->finishRoute();
		self::assertLessThanOrEqual(2000, $g['state']['total']);
		self::assertGreaterThan(1900, $g['state']['total']);
		$best = $g['state']['total'];
		$g = $this->finishRoute(1);
		self::assertSame($best, $g['state']['total']);
		self::assertContains('eerste-uitzicht', $g['state']['medals']);
		self::assertSame($this->uid, $g['champions']['route']['uid']);
	}
	public function testAttemptReplayAndStaleRevisionAreRejected(): void
	{
		$g = $this->act('begin', ['game' => 'route', 'level' => 2]);
		$s = $this->raw();
		$values = [
			'revision' => $g['state']['revision'],
			'attempt' => $s['active']['id'],
			'move' => 'route',
			'path' => $s['active']['puzzle']['solution'],
		];
		$this->service->act($this->uid, 'Arcadetest', 'move', $values);
		$this->expectException(DomainException::class);
		$this->service->act($this->uid, 'Arcadetest', 'move', $values);
	}
	public function testPartialPuzzlesPersistAndCanBeResumed(): void
	{
		$g = $this->act('begin', ['game' => 'puzzle', 'level' => 3]);
		$tiles = $g['state']['active']['tiles'];
		$g = $this->act('move', [
			'attempt' => $g['state']['active']['id'],
			'move' => 'swap',
			'a' => 0,
			'b' => 1,
		]);
		$reloaded = (new PanoramaArcadeService($this->db))->snapshot($this->uid);
		[$tiles[0], $tiles[1]] = [$tiles[1], $tiles[0]];
		self::assertSame($tiles, $reloaded['state']['active']['tiles']);
		self::assertSame(1, $g['state']['active']['moves']);
		self::assertSame(0, $g['state']['total']);
	}
	public function testAllFiveBestScoresAndChampionsAreStored(): void
	{
		foreach (array_keys(PanoramaArcade::GAMES) as $kind) {
			$this->act('begin', ['game' => $kind, 'level' => 3]);
			$s = $this->raw();
			$a = PanoramaArcadeTest::complete($s['active']);
			// De finale zet gaat door de echte service; voorbereidende zetten blijven transactionele fixtures.
			if ($kind === 'route') {
				$input = ['move' => 'route', 'path' => $a['puzzle']['solution']];
			} elseif ($kind === 'puzzle') {
				$a['tiles'] = range(0, 24);
				[$a['tiles'][0], $a['tiles'][1]] = [$a['tiles'][1], $a['tiles'][0]];
				$a['moves'] = $a['minimum'] - 1;
				$input = ['move' => 'swap', 'a' => 0, 'b' => 1];
			} elseif ($kind === 'memory') {
				$a['matched'] = range(0, count($a['deck']) - 1);
				$last = count($a['deck']) - 1;
				$pair = array_search($a['deck'][$last], $a['deck'], true);
				$a['matched'] = array_values(array_diff($a['matched'], [$last, $pair]));
				$a['open'] = [$pair];
				$input = ['move' => 'flip', 'index' => $last];
			} elseif ($kind === 'spot') {
				$a['round'] = $a['rounds'] - 1;
				$a['answers'] = array_slice($a['answers'], 0, $a['round']);
				$input = [
					'move' => 'answer',
					'answer' => PanoramaArcade::view($a)['crop'],
				];
			} else {
				$a['round'] = $a['rounds'] - 1;
				$a['phase'] = 'idle';
				[$a] = PanoramaArcade::move($a, 'reveal', []);
				$seq = PanoramaArcade::view($a)['sequence'];
				$a['position'] = count($seq) - 1;
				$a['until'] = microtime(true) - 1;
				$input = ['move' => 'pattern', 'pattern' => $seq];
			}
			$s['active'] = $a;
			$this->db->update(
				'panorama_players',
				['state' => json_encode($s)],
				['uid' => $this->uid]
			);
			$g = $this->act('move', ['attempt' => $a['id']] + $input);
			self::assertNull($g['state']['active']);
			self::assertArrayHasKey($kind, $g['state']['records']);
		}
		self::assertCount(5, $g['state']['records']);
		self::assertContains('vijfkamper', $g['state']['medals']);
		self::assertSame(10000, $g['state']['total']);
		self::assertCount(5, $g['champions']);
	}
	public function testPreviousExpeditionIsPreservedButNotPublishedAsNewScore(): void
	{
		$old = ['version' => 1, 'seed' => 'private-old-seed', 'score' => 1234];
		$this->db->insert('panorama_players', [
			'uid' => $this->uid,
			'display_name' => 'Arcadetest',
			'state' => json_encode($old),
			'score' => 1234,
			'updated_at' => '2000-01-01 00:00:00',
		]);
		self::assertNull($this->service->snapshot($this->uid)['state']);
		$g = $this->act('begin', ['game' => 'memory', 'level' => 1]);
		self::assertSame(0, $g['state']['total']);
		self::assertSame($old, $this->raw()['previousExpedition']);
		self::assertStringNotContainsString('private-old-seed', json_encode($g));
	}
}
