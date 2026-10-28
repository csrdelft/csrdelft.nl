<?php

declare(strict_types=1);
namespace CsrDelft\tests\service\panorama;
use CsrDelft\service\panorama\PanoramaDailyQuiz;
use CsrDelft\service\panorama\PanoramaArcadeService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use DomainException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
final class PanoramaDailyQuizTest extends TestCase
{
	private Connection $db;
	private PanoramaDailyQuiz $quiz;
	private string $uid;
	private string $root;
	protected function setUp(): void
	{
		$url = getenv('PANORAMA_TEST_DATABASE_URL');
		if (!$url) {
			$this->markTestSkipped('Lokale Panorama-testdatabase vereist.');
		}
		$this->db = DriverManager::getConnection(['url' => $url]);
		$this->db->setNestTransactionsWithSavepoints(true);
		$this->db->beginTransaction();
		$this->uid = 'daily_test_' . bin2hex(random_bytes(4));
		$this->root = dirname(__DIR__, 4) . '/.local/' . $this->uid;
		$this->quiz = new class (
			$this->db,
			new ParameterBag(['kernel.project_dir' => $this->root])
		) extends PanoramaDailyQuiz {
			public function today(): string
			{
				return '2090-05-20';
			}
		};
	}
	protected function tearDown(): void
	{
		if (isset($this->db)) {
			while ($this->db->getTransactionNestingLevel() > 0) {
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
	private function save(array $values = [], ?UploadedFile $file = null): int
	{
		return $this->quiz->save(
			$this->uid,
			$values + [
				'id' => 0,
				'revision' => 0,
				'day' => '2090-05-20',
				'prompt' => 'Welke stad zie je?',
				'answers' => "Parijs\nParis",
				'credit' => 'Fotograaf',
				'published' => 1,
			],
			$file
		);
	}
	private function guess(string $guess): array
	{
		$q = $this->quiz->snapshot($this->uid)['question'];
		return $this->quiz->guess($this->uid, 'Dagvraagtest', [
			'question' => $q['id'],
			'revision' => $q['revision'],
			'answerRevision' => $q['answerRevision'],
			'guess' => $guess,
		]);
	}
	public function testSpellingAccentsAliasesAndTranspositions(): void
	{
		foreach (['parijs', 'PARIS', ' parsi ', 'Parjjs'] as $guess) {
			self::assertTrue(PanoramaDailyQuiz::accepts($guess, ['Parijs', 'Paris']));
		}
		self::assertTrue(PanoramaDailyQuiz::accepts('Sao Paulo', ['São Paulo']));
		self::assertTrue(
			PanoramaDailyQuiz::accepts('Lodnon', ['London', 'Londen'])
		);
		self::assertFalse(PanoramaDailyQuiz::accepts('Riga', ['Rome']));
		self::assertFalse(PanoramaDailyQuiz::accepts('Par', ['Paris']));
		self::assertFalse(PanoramaDailyQuiz::accepts('Amsterdam', ['Paris']));
	}
	public function testOnlyPublishedTodayIsVisibleAndNoAnswersLeak(): void
	{
		$future = $this->save(['day' => '2090-05-21']);
		self::assertNull($this->quiz->snapshot($this->uid)['question']);
		$id = $this->save();
		$v = $this->quiz->snapshot($this->uid);
		self::assertSame($id, $v['question']['id']);
		self::assertNull($v['question']['answer']);
		self::assertSame('', $v['question']['credit']);
		self::assertArrayNotHasKey('answers', $v['question']);
		self::assertNotSame($future, $id);
	}
	public function testPointsAwardedExactlyOnceAndIncludedInLeaderboard(): void
	{
		$this->save();
		$v = $this->guess('parsi');
		self::assertTrue($v['question']['solved']);
		self::assertSame(200, $v['totalPoints']);
		$all = (new PanoramaArcadeService($this->db))->snapshot($this->uid);
		$row = array_values(
			array_filter($all['leaderboard'], fn($r) => $r['uid'] === $this->uid)
		)[0];
		self::assertSame(200, (int) $row['score']);
		$this->expectException(DomainException::class);
		$this->guess('Paris');
	}
	public function testThreeAttemptsAndNoRepeatedGuess(): void
	{
		$this->save();
		$this->guess('Tokyo');
		try {
			$this->guess(' TOKYO ');
			self::fail('Herhaalde gok geaccepteerd');
		} catch (DomainException $e) {
			self::assertStringContainsString('al geprobeerd', $e->getMessage());
		}
		$this->guess('Madrid');
		$v = $this->guess('Oslo');
		self::assertSame(0, $v['question']['remaining']);
		self::assertSame('Parijs', $v['question']['answer']);
		self::assertSame(0, $v['totalPoints']);
		$this->expectException(DomainException::class);
		$this->guess('Paris');
	}
	public function testSecondGuessReceives120Points(): void
	{
		$this->save();
		$this->guess('Tokyo');
		$v = $this->guess('Paris');
		self::assertSame(120, $v['totalPoints']);
	}
	public function testPlayedQuestionsCannotBeEditedOrMoved(): void
	{
		$id = $this->save();
		$this->guess('Tokyo');
		$q = $this->quiz->questions()[0];
		$this->expectException(DomainException::class);
		$this->save([
			'id' => $id,
			'revision' => $q['revision'],
			'day' => '2090-05-21',
		]);
	}
	public function testInvalidDateAndDuplicateDayAreRejected(): void
	{
		$this->save();
		try {
			$this->save(['day' => '2090-02-30']);
			self::fail('Ongeldige datum');
		} catch (DomainException $e) {
			self::assertStringContainsString('dag', $e->getMessage());
		}
		$this->expectException(DomainException::class);
		$this->save();
	}
	public function testFuturePhotoIsOnlyAvailableToManagerAndFilenameIsOpaque(): void
	{
		$source = dirname(__DIR__, 4) . '/htdocs/images/lustrum13/fotos/f08.webp';
		$file = new UploadedFile(
			$source,
			'Paris-answer.php.jpg',
			'image/webp',
			null,
			true
		);
		$id = $this->save(['day' => '2090-05-21'], $file);
		self::assertNull($this->quiz->photo($id, false));
		$path = $this->quiz->photo($id, true);
		self::assertNotNull($path);
		self::assertMatchesRegularExpression('/[a-f0-9]{40}\.webp$/', $path);
		self::assertStringNotContainsString('Paris', $path);
		$q = $this->quiz->questions()[0];
		self::assertSame('/panorama/spellen/dagfoto/' . $id, $q['image']);
	}
	public function testForgedImageIsRejected(): void
	{
		$file = new UploadedFile(__FILE__, 'fake.jpg', 'image/jpeg', null, true);
		$this->expectException(DomainException::class);
		$this->save([], $file);
	}

	public function testDailyStatisticsCountPlayersNotGuesses(): void
	{
		$this->save();
		$v = $this->quiz->snapshot($this->uid);
		self::assertSame(
			[
				'participants' => 0,
				'correct' => 0,
				'correctPercent' => null,
				'firstTry' => 0,
				'showParticipants' => false,
				'showFirstTry' => false,
			],
			$v['question']['statistics']
		);
		$v = $this->guess('Rome');
		self::assertSame(1, $v['question']['statistics']['participants']);
		self::assertSame(0, $v['question']['statistics']['correctPercent']);
		$v = $this->guess('Parijs');
		self::assertSame(1, $v['question']['statistics']['participants']);
		self::assertSame(100, $v['question']['statistics']['correctPercent']);
		self::assertSame(0, $v['question']['statistics']['firstTry']);
		$uid = $this->uid . '_b';
		$q = $this->quiz->snapshot($uid)['question'];
		$this->quiz->guess($uid, 'Tweede speler', [
			'question' => $q['id'],
			'revision' => $q['revision'],
			'answerRevision' => 0,
			'guess' => 'Berlijn',
		]);
		$v = $this->quiz->snapshot($uid);
		self::assertSame(2, $v['question']['statistics']['participants']);
		self::assertSame(50, $v['question']['statistics']['correctPercent']);
		self::assertNull($v['question']['answer']);
		// Een derde speler raadt het direct: twee van drie deelnemers hebben het goed.
		$uid = $this->uid . '_c';
		$q = $this->quiz->snapshot($uid)['question'];
		$v = $this->quiz->guess($uid, 'Derde speler', [
			'question' => $q['id'],
			'revision' => $q['revision'],
			'answerRevision' => 0,
			'guess' => 'Paris',
		]);
		self::assertSame(
			[
				'participants' => 3,
				'correct' => 2,
				'correctPercent' => 67,
				'firstTry' => 1,
				'showParticipants' => false,
				'showFirstTry' => false,
			],
			$v['question']['statistics']
		);
		self::assertSame(
			$v['question']['statistics'],
			$this->quiz->snapshot($this->uid)['question']['statistics']
		);
	}
}
