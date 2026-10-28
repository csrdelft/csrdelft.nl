<?php

declare(strict_types=1);
namespace CsrDelft\tests\service\panorama;
use CsrDelft\service\panorama\{
	PanoramaArcade,
	PanoramaArcadeService,
	PanoramaDailyQuiz,
	PanoramaGameSettings,
	PanoramaRoute
};
use Doctrine\DBAL\{Connection, DriverManager};
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
final class PanoramaGameSettingsTest extends TestCase
{
	private Connection $db;
	private PanoramaGameSettings $settings;
	private string $root;
	private string $uid;
	protected function setUp(): void
	{
		$url = getenv('PANORAMA_TEST_DATABASE_URL');
		if (!$url) {
			$this->markTestSkipped('Lokale testdatabase vereist.');
		}
		$this->db = DriverManager::getConnection(['url' => $url]);
		$this->db->setNestTransactionsWithSavepoints(true);
		$this->db->beginTransaction();
		$this->db->update(
			'panorama_game_settings',
			['settings' => '{}'],
			['id' => 1]
		);
		$this->uid = 'games_test_' . bin2hex(random_bytes(4));
		$this->root = dirname(__DIR__, 4) . '/.local/' . $this->uid;
		$quiz = new PanoramaDailyQuiz(
			$this->db,
			new ParameterBag(['kernel.project_dir' => $this->root])
		);
		$this->settings = new PanoramaGameSettings($this->db, $quiz);
	}
	protected function tearDown(): void
	{
		if (isset($this->db)) {
			while ($this->db->getTransactionNestingLevel()) {
				$this->db->rollBack();
			}
			$this->db->close();
		}
		if (isset($this->root) && is_dir($this->root . '/data/panorama-quiz')) {
			foreach (glob($this->root . '/data/panorama-quiz/*.webp') as $file) {
				unlink($file);
			}
			rmdir($this->root . '/data/panorama-quiz');
			rmdir($this->root . '/data');
			rmdir($this->root);
		}
	}
	private function save(array $input): array
	{
		$g = $this->settings->get();
		return $this->settings->save(
			$this->uid,
			$input + [
				'revision' => (string) $g['revision'],
				'game' => 'puzzle',
				'name' => 'Uitzicht',
				'enabled' => '1',
				'images' => $g['games']['puzzle']['images'],
			],
			null
		);
	}
	public function testSimpleRoutesHaveNoResourcesAndRemainSolvable(): void
	{
		for ($level = 1; $level <= 3; $level++) {
			for ($i = 0; $i < 8; $i++) {
				$p = PanoramaRoute::create('simple' . $i, $level);
				self::assertCount($level === 1 ? 1 : 2, $p['targets']);
				self::assertLessThanOrEqual(25, count($p['nodes']));
				foreach ($p['edges'] as $e) {
					self::assertSame(0, $e['water']);
					self::assertFalse($e['locked']);
					self::assertLessThanOrEqual(3, $e['cost']);
				}
				self::assertNotNull(PanoramaRoute::evaluate($p, $p['solution']));
			}
		}
	}
	public function testClosedGameCannotBeStartedAndRenameIsVisible(): void
	{
		$s = $this->save(['enabled' => '', 'name' => 'Nieuw uitzicht']);
		self::assertFalse($s['games']['puzzle']['enabled']);
		$service = new PanoramaArcadeService($this->db, $this->settings);
		self::assertSame(
			'Nieuw uitzicht',
			$service->snapshot($this->uid)['games']['puzzle']['name']
		);
		$this->expectException(DomainException::class);
		$service->act($this->uid, 'Test', 'begin', [
			'game' => 'puzzle',
			'level' => 1,
			'revision' => 0,
		]);
	}
	public function testStaleSettingsAreRejected(): void
	{
		$this->save([]);
		$this->expectException(DomainException::class);
		$this->save(['revision' => '0']);
	}
	public function testMemoryNeedsEnoughUniqueImages(): void
	{
		$this->expectException(DomainException::class);
		$this->save(['game' => 'memory', 'images' => ['bos', 'bos', 'meer']]);
	}
	public function testSpotRejectsDrawings(): void
	{
		$this->expectException(DomainException::class);
		$this->save([
			'game' => 'spot',
			'images' => ['bos', 'meer', 'nacht', 'kloof'],
		]);
	}
	public function testChangingPicturesArchivesOnlyAffectedRecords(): void
	{
		$s = PanoramaArcade::initial();
		$s['records'] = [
			'puzzle' => ['best' => 1000, 'plays' => 1, 'fastestHard' => 100],
			'memory' => ['best' => 900, 'plays' => 1, 'fastestHard' => 200],
		];
		$this->db->insert('panorama_players', [
			'uid' => $this->uid,
			'display_name' => 'Test',
			'state' => json_encode($s),
			'revision' => 0,
			'score' => 1900,
			'stage' => 2,
			'updated_at' => '2026-01-01 00:00:00',
		]);
		$this->save(['images' => ['meer']]);
		$row = $this->db->fetchAssociative(
			'SELECT * FROM panorama_players WHERE uid=?',
			[$this->uid]
		);
		$s = json_decode($row['state'], true);
		self::assertArrayNotHasKey('puzzle', $s['records']);
		self::assertSame(900, $s['records']['memory']['best']);
		self::assertSame(
			1000,
			$s['retiredRecords']['settings'][0]['record']['best']
		);
		self::assertSame(900, (int) $row['score']);
	}
	public function testUploadCanBeSelectedAndUsedByGame(): void
	{
		$file = new UploadedFile(
			dirname(__DIR__, 4) . '/htdocs/images/lustrum13/fotos/f01.webp',
			'test.webp',
			'image/webp',
			null,
			true
		);
		$s = $this->settings->save(
			$this->uid,
			['revision' => '0', 'credit' => 'Testfotograaf'],
			$file
		);
		$id = array_key_first($s['uploads']);
		self::assertMatchesRegularExpression('/^u_[a-f0-9]{40}$/D', $id);
		self::assertFileExists($this->settings->photo(substr($id, 2)));
		self::assertNull($this->settings->photo('../secret'));
		$s = $this->save(['images' => [$id]]);
		$a = PanoramaArcade::begin('puzzle', 1, 'test', $s['games']['puzzle']);
		self::assertSame($id, $a['art']);
	}

	public function testRouteMigrationKeepsArchivedScoreAndOtherActiveGame(): void
	{
		require_once dirname(__DIR__, 4) .
			'/db/doctrine_migrations/Version20261028140000.php';
		$state = PanoramaArcade::initial();
		$state['records'] = [
			'route' => ['best' => 1000],
			'memory' => ['best' => 500],
		];
		$state['active'] = PanoramaArcade::begin('memory', 1, 'test');
		$this->db->insert('panorama_players', [
			'uid' => $this->uid,
			'display_name' => 'Test',
			'state' => json_encode($state),
			'revision' => 0,
			'score' => 1500,
			'stage' => 2,
			'updated_at' => '2026-01-01 00:00:00',
		]);
		$migration = new \DoctrineMigrations\Version20261028140000(
			$this->db,
			new \Psr\Log\NullLogger()
		);
		$migration->up(new \Doctrine\DBAL\Schema\Schema());
		foreach (array_slice($migration->getSql(), 2) as $query) {
			$this->db->executeStatement($query->getStatement());
		}
		$row = $this->db->fetchAssociative(
			'SELECT state,score FROM panorama_players WHERE uid=?',
			[$this->uid]
		);
		$state = json_decode($row['state'], true);
		self::assertSame(1000, $state['retiredRecords']['routeV2']['best']);
		self::assertArrayNotHasKey('route', $state['records']);
		self::assertSame('memory', $state['active']['kind']);
		self::assertSame(500, (int) $row['score']);
	}

	public function testBackgroundChangeKeepsScores(): void
	{
		$s = PanoramaArcade::initial();
		$s['records'] = [
			'stars' => ['best' => 1200, 'plays' => 1, 'fastestHard' => 15000],
		];
		$this->db->insert('panorama_players', [
			'uid' => $this->uid,
			'display_name' => 'Test',
			'state' => json_encode($s),
			'revision' => 0,
			'score' => 1200,
			'stage' => 1,
			'updated_at' => '2026-01-01 00:00:00',
		]);
		$this->save(['game' => 'stars', 'images' => ['bos']]);
		$row = $this->db->fetchAssociative(
			'SELECT state,score FROM panorama_players WHERE uid=?',
			[$this->uid]
		);
		self::assertSame(1200, (int) $row['score']);
		self::assertSame(
			15000,
			json_decode($row['state'], true)['records']['stars']['fastestHard']
		);
	}

	public function testDailyCountVisibilityDefaultsOffAndCanBeSetIndependently(): void
	{
		self::assertSame(
			['participants' => false, 'firstTry' => false],
			$this->settings->get()['dailyVisibility']
		);
		$s = $this->save(['game' => 'daily-visibility', 'showFirstTry' => '1']);
		self::assertSame(
			['participants' => false, 'firstTry' => true],
			$s['dailyVisibility']
		);
		$s = $this->save(['game' => 'daily-visibility', 'showParticipants' => '1']);
		self::assertSame(
			['participants' => true, 'firstTry' => false],
			$s['dailyVisibility']
		);
	}
}
