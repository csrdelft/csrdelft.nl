<?php
declare(strict_types=1);

use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\entity\pin\PinTransactie;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use CsrDelft\service\pin\PinTransactieMatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class PinTransactieMatcherTest extends TestCase
{
	private function createMatcher()
	{
		return new PinTransactieMatcher(
			$this->createMock(EntityManagerInterface::class),
			$this->createMock(PinTransactieMatchRepository::class)
		);
	}

	public function testMatch()
	{
		$transacties = [
			$this->trans(0, 100),
			$this->trans(2, 236),
			$this->trans(3, 42), #missende bestelling
			$this->trans(4, 1115),
			$this->trans(5, 16), #verkeerd bedrag
		];

		$bestellingen = [
			$this->best(100, 100),
			$this->best(101, 14), # missende transactie (en B'vo)
			$this->best(102, 236),
			$this->best(104, 1115),
			$this->best(105, 20), #verkeerd bedrag
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertMatch(
			$matches,
			$transacties[0],
			$bestellingen[0],
			PinTransactieMatchStatusEnum::STATUS_MATCH
		);
		$this->assertMatch(
			$matches,
			$transacties[1],
			$bestellingen[2],
			PinTransactieMatchStatusEnum::STATUS_MATCH
		);
		$this->assertMatch(
			$matches,
			$transacties[3],
			$bestellingen[3],
			PinTransactieMatchStatusEnum::STATUS_MATCH
		);
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[1],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
		$this->assertMatch(
			$matches,
			$transacties[2],
			null,
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING
		);
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[1],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
		$this->assertMatch(
			$matches,
			$transacties[4],
			null,
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING
		);
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[4],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
	}

	public function testMatchDifferentLength()
	{
		$transacties = [$this->trans(1, 100)];

		$bestellingen = [
			$this->best(100, 27), #missende transactie
			$this->best(101, 100),
			$this->best(105, 20), #missende transactie
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertMatch(
			$matches,
			$transacties[0],
			$bestellingen[1],
			PinTransactieMatchStatusEnum::STATUS_MATCH
		);
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[2],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[0],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
	}

	public function testMatchDifferentLength2()
	{
		$transacties = [
			$this->trans(0, 170), # missende bestelling
			$this->trans(1, 100),
			$this->trans(2, 200), # missende bestelling
		];

		$bestellingen = [$this->best(101, 100)];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertMatch(
			$matches,
			$transacties[1],
			$bestellingen[0],
			PinTransactieMatchStatusEnum::STATUS_MATCH
		);
		$this->assertMatch(
			$matches,
			$transacties[0],
			null,
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING
		);
		$this->assertMatch(
			$matches,
			$transacties[2],
			null,
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING
		);
	}

	public function testRealLifeExample()
	{
		$transacties = [
			$this->trans(0, 18, '2023-02-02 16:23:48'),
			$this->trans(1, 2000, '2023-02-02 21:02:53'),
			$this->trans(2, 3000, '2023-02-02 22:14:51'),
			$this->trans(3, 5000, '2023-02-02 22:18:45'),
			$this->trans(4, 1000, '2023-02-02 22:21:15'),
			$this->trans(5, 10000, '2023-02-02 22:27:48'),
			$this->trans(6, 360, '2023-02-02 22:33:24'),
			$this->trans(7, 4000, '2023-02-02 22:38:51'),
			$this->trans(8, 6000, '2023-02-02 22:57:32'),
			$this->trans(9, 2000, '2023-02-02 23:32:27'),
			$this->trans(10, 10000, '2023-02-02 23:59:41'),
			$this->trans(11, 6919, '2023-02-03 00:24:41'),
			$this->trans(12, 3000, '2023-02-03 00:38:52'),
			$this->trans(13, 5000, '2023-02-03 02:44:32'),
		];

		$bestellingen = [
			$this->best(0, 2235, '2023-02-02 14:37:50'),
			$this->best(1, 18, '2023-02-02 16:24:10'),
			$this->best(2, 2000, '2023-02-02 21:03:19'),
			$this->best(3, 3000, '2023-02-02 22:16:30'),
			$this->best(4, 5000, '2023-02-02 22:18:34'),
			$this->best(5, 1000, '2023-02-02 22:21:15'),
			$this->best(6, 10000, '2023-02-02 22:28:25'),
			$this->best(7, 360, '2023-02-02 22:34:11'),
			$this->best(8, 4000, '2023-02-02 22:38:27'),
			$this->best(9, 6000, '2023-02-02 22:57:37'),
			$this->best(10, 2000, '2023-02-02 23:32:13'),
			$this->best(11, 10000, '2023-02-02 23:59:40'),
			$this->best(12, 6919, '2023-02-03 00:25:05'),
			$this->best(13, 3000, '2023-02-03 00:38:53'),
			$this->best(14, 5000, '2023-02-03 02:45:39'),
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties(array_reverse($transacties));
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();

		// Transacties 0 tot 13 matchen met bestelling 1 tot 14
		foreach ($matches as $match) {
			$transactie = $match->transactie ? $match->transactie->id : 'niet';
			$bestelling = $match->bestelling ? $match->bestelling->id : 'geen';

			echo "Transactie {$transactie} gematcht met bestelling {$bestelling}\n";
		}

		for ($i = 0; $i <= 13; $i++) {
			$this->assertMatch(
				$matches,
				$transacties[$i],
				$bestellingen[$i + 1],
				PinTransactieMatchStatusEnum::STATUS_MATCH
			);
		}

		// Bestelling 0 mist een transactie
		$this->assertMatch(
			$matches,
			null,
			$bestellingen[0],
			PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE
		);
	}

	private function trans($id, $bedrag, $date = null)
	{
		$transactie = new PinTransactie();
		$transactie->id = $id;
		$transactie->amount = 'EUR ' . $bedrag;
		if ($date != null) {
			$transactie->datetime = DateTimeImmutable::createFromFormat(
				'Y-m-d H:i:s',
				$date
			);
		}
		return $transactie;
	}

	private function best($id, $bedrag, $date = null)
	{
		$bestellingInhoud = new CiviBestellingInhoud();
		$bestellingInhoud->aantal = $bedrag;
		$bestellingInhoud->bestelling_id = $id;
		$bestellingInhoud->product_id = CiviProductTypeEnum::PINTRANSACTIE;

		$bestelling = new CiviBestelling();
		$bestelling->id = $id;
		$bestelling->inhoud->add($bestellingInhoud);
		if ($date != null) {
			$bestelling->moment = DateTimeImmutable::createFromFormat(
				'Y-m-d H:i:s',
				$date
			);
		}

		return $bestelling;
	}

	private function assertMatch($matches, $transactie, $bestelling, $status)
	{
		$transactieT = $transactie ? 'transactie ' . $transactie->id : '';
		$bestellingT = $bestelling ? 'bestelling ' . $bestelling->id : '';
		$this->assertTrue(
			$this->hasMatch($matches, $transactie, $bestelling, $status),
			"Verwachte match niet gevonden: {$transactieT} {$status} {$bestellingT}"
		);
	}

	/**
	 * @param $matches PinTransactieMatch[]
	 * @param $transactie
	 * @param $bestelling
	 * @param $status
	 * @return bool
	 */
	private function hasMatch($matches, $transactie, $bestelling, $status)
	{
		foreach ($matches as $match) {
			if (
				$match->transactie == $transactie &&
				$match->bestelling == $bestelling &&
				$match->status == $status
			) {
				return true;
			}
		}
		return false;
	}
}
