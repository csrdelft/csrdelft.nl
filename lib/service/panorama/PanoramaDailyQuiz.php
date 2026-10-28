<?php

declare(strict_types=1);
namespace CsrDelft\service\panorama;
use Doctrine\DBAL\Connection;
use DomainException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PanoramaDailyQuiz
{
	public function __construct(
		private readonly Connection $connection,
		private readonly ParameterBagInterface $parameters
	) {
	}
	public function today(): string
	{
		return (new \DateTimeImmutable(
			'now',
			new \DateTimeZone('Europe/Amsterdam')
		))->format('Y-m-d');
	}
	public static function normalize(string $text): string
	{
		$text = mb_strtolower(trim($text), 'UTF-8');
		$ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
		return preg_replace(
			'/[^a-z0-9]/',
			'',
			strtolower($ascii === false ? $text : $ascii)
		);
	}
	public static function accepts(string $guess, array $answers): bool
	{
		$g = self::normalize($guess);
		if ($g === '') {
			return false;
		}
		foreach ($answers as $answer) {
			$a = self::normalize($answer);
			if ($a === $g) {
				return true;
			}
			$limit = strlen($a) >= 9 ? 2 : (strlen($a) >= 5 ? 1 : 0);
			if (!$limit || abs(strlen($a) - strlen($g)) > $limit) {
				continue;
			}
			$d = [];
			for ($i = 0; $i <= strlen($g); $i++) {
				$d[$i][0] = $i;
			}
			for ($j = 0; $j <= strlen($a); $j++) {
				$d[0][$j] = $j;
			}
			for ($i = 1; $i <= strlen($g); $i++) {
				for ($j = 1; $j <= strlen($a); $j++) {
					$d[$i][$j] = min(
						$d[$i - 1][$j] + 1,
						$d[$i][$j - 1] + 1,
						$d[$i - 1][$j - 1] + ($g[$i - 1] === $a[$j - 1] ? 0 : 1)
					);
					if (
						$i > 1 &&
						$j > 1 &&
						$g[$i - 1] === $a[$j - 2] &&
						$g[$i - 2] === $a[$j - 1]
					) {
						$d[$i][$j] = min($d[$i][$j], $d[$i - 2][$j - 2] + 1);
					}
				}
			}
			if ($d[strlen($g)][strlen($a)] <= $limit) {
				return true;
			}
		}
		return false;
	}
	public function snapshot(string $uid): array
	{
		$day = $this->today();
		$q = $this->connection->fetchAssociative(
			'SELECT * FROM panorama_daily_questions WHERE day=? AND published=1',
			[$day]
		);
		$total = (int) $this->connection->fetchOne(
			'SELECT COALESCE(SUM(points),0) FROM panorama_daily_answers WHERE uid=?',
			[$uid]
		);
		$v = ['day' => $day, 'question' => null, 'totalPoints' => $total];
		if (!$q) {
			return $v;
		}
		$a = $this->connection->fetchAssociative(
			'SELECT * FROM panorama_daily_answers WHERE question_id=? AND uid=?',
			[$q['id'], $uid]
		);
		$guesses = $a
			? json_decode($a['guesses'], true, 16, JSON_THROW_ON_ERROR)
			: [];
		$ended = (bool) ($a['solved'] ?? false) || count($guesses) >= 3;
		$stats = $this->connection->fetchAssociative(
			'SELECT COUNT(*) AS participants, COALESCE(SUM(solved),0) AS correct, COALESCE(SUM(CASE WHEN solved=1 AND JSON_LENGTH(guesses)=1 THEN 1 ELSE 0 END),0) AS first_try FROM panorama_daily_answers WHERE question_id=?',
			[$q['id']]
		);
		$participants = (int) $stats['participants'];
		$settings = json_decode(
			$this->connection->fetchOne(
				'SELECT settings FROM panorama_game_settings WHERE id=1'
			) ?:
			'{}',
			true,
			32,
			JSON_THROW_ON_ERROR
		);
		$visibility = ($settings['dailyVisibility'] ?? []) + [
			'participants' => false,
			'firstTry' => false,
		];
		$v['question'] = [
			'id' => (int) $q['id'],
			'statistics' => [
				'participants' => $participants,
				'correct' => (int) $stats['correct'],
				'correctPercent' => $participants
					? (int) round((100 * (int) $stats['correct']) / $participants)
					: null,
				'firstTry' => (int) $stats['first_try'],
				'showParticipants' => $visibility['participants'],
				'showFirstTry' => $visibility['firstTry'],
			],
			'revision' => (int) $q['revision'],
			'prompt' => $q['prompt'],
			'image' => $q['image'] ? '/panorama/spellen/dagfoto/' . $q['id'] : null,
			'guesses' => $guesses,
			'remaining' => max(0, 3 - count($guesses)),
			'solved' => (bool) ($a['solved'] ?? false),
			'points' => (int) ($a['points'] ?? 0),
			'answerRevision' => (int) ($a['revision'] ?? 0),
			'answer' => $ended
				? json_decode($q['answers'], true, 16, JSON_THROW_ON_ERROR)[0]
				: null,
			'credit' => $ended ? $q['credit'] : '',
		];
		return $v;
	}
	public function guess(string $uid, string $name, array $input): array
	{
		$id = $input['question'] ?? null;
		$guess = $input['guess'] ?? null;
		if (
			!is_int($id) ||
			!is_string($guess) ||
			mb_strlen($guess) > 100 ||
			mb_strlen(trim($guess)) < 2
		) {
			throw new DomainException('Vul je antwoord in.');
		}
		$this->connection->beginTransaction();
		try {
			$q = $this->connection->fetchAssociative(
				'SELECT * FROM panorama_daily_questions WHERE id=? FOR UPDATE',
				[$id]
			);
			if (!$q || $q['day'] !== $this->today() || !$q['published']) {
				throw new DomainException(
					'Deze vraag is niet de dagvraag van vandaag. Vernieuw de pagina.'
				);
			}
			if (($input['revision'] ?? null) !== (int) $q['revision']) {
				throw new DomainException(
					'De vraag is bijgewerkt. Vernieuw de pagina.'
				);
			}
			$a = $this->connection->fetchAssociative(
				'SELECT * FROM panorama_daily_answers WHERE question_id=? AND uid=? FOR UPDATE',
				[$id, $uid]
			);
			$guesses = $a
				? json_decode($a['guesses'], true, 16, JSON_THROW_ON_ERROR)
				: [];
			if (($input['answerRevision'] ?? null) !== (int) ($a['revision'] ?? 0)) {
				throw new DomainException(
					'Je antwoord is al verwerkt. Vernieuw de pagina.'
				);
			}
			if (($a && $a['solved']) || count($guesses) >= 3) {
				throw new DomainException('Je hebt deze dagvraag al afgerond.');
			}
			$normalized = self::normalize($guess);
			if ($normalized === '') {
				throw new DomainException('Vul je antwoord in.');
			}
			foreach ($guesses as $old) {
				if (self::normalize($old) === $normalized) {
					throw new DomainException('Dit antwoord had je al geprobeerd.');
				}
			}
			$guesses[] = trim($guess);
			$solved = self::accepts(
				$guess,
				json_decode($q['answers'], true, 16, JSON_THROW_ON_ERROR)
			);
			$points = $solved ? [1 => 200, 2 => 120, 3 => 60][count($guesses)] : 0;
			$fields = [
				'display_name' => mb_substr($name, 0, 160),
				'guesses' => json_encode($guesses, JSON_THROW_ON_ERROR),
				'solved' => (int) $solved,
				'points' => $points,
				'answered_at' =>
					$solved || count($guesses) === 3 ? date('Y-m-d H:i:s') : null,
				'revision' => (int) ($a['revision'] ?? 0) + 1,
			];
			if ($a) {
				$this->connection->update('panorama_daily_answers', $fields, [
					'question_id' => $id,
					'uid' => $uid,
				]);
			} else {
				$this->connection->insert(
					'panorama_daily_answers',
					['question_id' => $id, 'uid' => $uid] + $fields
				);
			}
			$this->connection->commit();
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			throw $e;
		}
		return $this->snapshot($uid);
	}
	public function questions(): array
	{
		$rows = $this->connection->fetchAllAssociative(
			'SELECT q.*, (SELECT COUNT(*) FROM panorama_daily_answers a WHERE a.question_id=q.id) AS played FROM panorama_daily_questions q ORDER BY day DESC LIMIT 180'
		);
		foreach ($rows as &$q) {
			$q['id'] = (int) $q['id'];
			$q['revision'] = (int) $q['revision'];
			$q['published'] = (bool) $q['published'];
			$q['played'] = (int) $q['played'];
			$q['answers'] = json_decode($q['answers'], true, 16, JSON_THROW_ON_ERROR);
			$q['image'] = $q['image']
				? '/panorama/spellen/dagfoto/' . $q['id']
				: null;
		}
		unset($q);
		return $rows;
	}
	public function save(string $uid, array $input, ?UploadedFile $file): int
	{
		$id = filter_var($input['id'] ?? 0, FILTER_VALIDATE_INT);
		$revision = filter_var($input['revision'] ?? 0, FILTER_VALIDATE_INT);
		$day = $input['day'] ?? '';
		if ($id === false || $id < 0 || $revision === false || $revision < 0) {
			throw new DomainException('Ongeldige vraagversie.');
		}
		$parsedDay = is_string($day)
			? \DateTimeImmutable::createFromFormat('!Y-m-d', $day)
			: false;
		$prompt = trim((string) ($input['prompt'] ?? ''));
		$raw = $input['answers'] ?? '';
		$credit = trim((string) ($input['credit'] ?? ''));
		if (
			!is_string($day) ||
			!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day) ||
			!$parsedDay ||
			$parsedDay->format('Y-m-d') !== $day ||
			$day < $this->today()
		) {
			throw new DomainException('Kies vandaag of een komende dag.');
		}
		if (
			mb_strlen($prompt) < 3 ||
			mb_strlen($prompt) > 180 ||
			mb_strlen($credit) > 255 ||
			!is_string($raw) ||
			strlen($raw) > 2000
		) {
			throw new DomainException('Controleer de vraag en antwoorden.');
		}
		$answers = array_values(
			array_unique(
				array_filter(array_map('trim', preg_split('/[\r\n|,]+/', $raw)))
			)
		);
		if (count($answers) < 1 || count($answers) > 15) {
			throw new DomainException('Vul 1 tot 15 geaccepteerde antwoorden in.');
		}
		foreach ($answers as $answer) {
			if (mb_strlen($answer) > 100 || strlen(self::normalize($answer)) < 2) {
				throw new DomainException('Een antwoord moet 2 tot 100 tekens hebben.');
			}
		}
		$this->connection->beginTransaction();
		try {
			$q = $id
				? $this->connection->fetchAssociative(
					'SELECT * FROM panorama_daily_questions WHERE id=? FOR UPDATE',
					[$id]
				)
				: null;
			if ($id && !$q) {
				throw new DomainException('Deze vraag bestaat niet meer.');
			}
			if ($q && (int) $q['revision'] !== $revision) {
				throw new DomainException(
					'Deze vraag is intussen door iemand anders bijgewerkt.'
				);
			}
			if (
				$q &&
				$this->connection->fetchOne(
					'SELECT 1 FROM panorama_daily_answers WHERE question_id=? LIMIT 1',
					[$id]
				)
			) {
				throw new DomainException('Deze vraag is al gespeeld en staat vast.');
			}
			if (
				$this->connection->fetchOne(
					'SELECT id FROM panorama_daily_questions WHERE day=? AND id<>?',
					[$day, $id]
				)
			) {
				throw new DomainException('Er staat al een vraag op deze dag.');
			}
			$image = $q['image'] ?? null;
			if (!empty($input['removeImage'])) {
				$image = null;
			}
			if ($file) {
				$image = $this->storeImage($file);
			}
			$fields = [
				'day' => $day,
				'prompt' => $prompt,
				'answers' => json_encode($answers, JSON_THROW_ON_ERROR),
				'image' => $image,
				'credit' => $credit,
				'published' => !empty($input['published']) ? 1 : 0,
				'created_by' => $q['created_by'] ?? $uid,
				'revision' => (int) ($q['revision'] ?? 0) + 1,
				'updated_at' => date('Y-m-d H:i:s'),
			];
			if ($q) {
				$this->connection->update('panorama_daily_questions', $fields, [
					'id' => $id,
				]);
			} else {
				$this->connection->insert('panorama_daily_questions', $fields);
				$id = (int) $this->connection->lastInsertId();
			}
			$this->connection->commit();
		} catch (\Throwable $e) {
			$this->connection->rollBack();
			throw $e;
		}
		return $id;
	}
	private function directory(): string
	{
		return $this->parameters->get('kernel.project_dir') . '/data/panorama-quiz';
	}
	public function storeImage(UploadedFile $file): string
	{
		if (!$file->isValid() || $file->getSize() > 8 * 1024 * 1024) {
			throw new DomainException('Kies een afbeelding van maximaal 8 MB.');
		}
		$type = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getPathname());
		if (!in_array($type, ['image/jpeg', 'image/png', 'image/webp'], true)) {
			throw new DomainException('Gebruik een JPEG-, PNG- of WebP-afbeelding.');
		}
		$size = @getimagesize($file->getPathname());
		if (!$size || $size[0] * $size[1] > 20000000) {
			throw new DomainException('Deze afbeelding is te groot om te verwerken.');
		}
		$src = @imagecreatefromstring(file_get_contents($file->getPathname()));
		if (!$src) {
			throw new DomainException('Deze afbeelding kan niet worden gelezen.');
		}
		$scale = min(1, 1920 / max($size[0], $size[1]));
		$dst = imagecreatetruecolor(
			max(1, (int) ($size[0] * $scale)),
			max(1, (int) ($size[1] * $scale))
		);
		imagecopyresampled(
			$dst,
			$src,
			0,
			0,
			0,
			0,
			imagesx($dst),
			imagesy($dst),
			$size[0],
			$size[1]
		);
		$dir = $this->directory();
		if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
			throw new \RuntimeException('Kan de fotomap niet maken.');
		}
		$name = bin2hex(random_bytes(20)) . '.webp';
		if (!imagewebp($dst, $dir . '/' . $name, 85)) {
			throw new \RuntimeException('Kan de afbeelding niet opslaan.');
		}
		imagedestroy($src);
		imagedestroy($dst);
		return $name;
	}
	public function storedImage(string $name): ?string
	{
		if (!preg_match('/^[a-f0-9]{40}\.webp$/D', $name)) {
			return null;
		}
		$path = $this->directory() . '/' . $name;
		return is_file($path) ? $path : null;
	}
	public function photo(int $id, bool $manager): ?string
	{
		$q = $this->connection->fetchAssociative(
			'SELECT day,published,image FROM panorama_daily_questions WHERE id=?',
			[$id]
		);
		if (
			!$q ||
			!$q['image'] ||
			(!$manager && (!$q['published'] || $q['day'] > $this->today()))
		) {
			return null;
		}
		if (!preg_match('/^[a-f0-9]{40}\.webp$/D', $q['image'])) {
			return null;
		}
		$path = $this->directory() . '/' . $q['image'];
		return is_file($path) ? $path : null;
	}
}
