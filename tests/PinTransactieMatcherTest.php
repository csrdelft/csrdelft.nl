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

final class PinTransactieMatcherTest extends TestCase {
	private function createMatcher() {
		return new PinTransactieMatcher(
			$this->createMock(EntityManagerInterface::class),
			$this->createMock(PinTransactieMatchRepository::class)
		);
	}

	public function testMatch() {
		$transacties = [
			$this->trans(0, 100),
			$this->trans(2, 236),
			$this->trans(3, 42), #missende bestelling
			$this->trans(4, 1115),
			$this->trans(5, 16) #verkeerd bedrag
		];

		$bestellingen = [
			$this->best(100, 100),
			$this->best(101, 14), # missende transactie (en B'vo)
			$this->best(102, 236),
			$this->best(104, 1115),
			$this->best(105, 20) #verkeerd bedrag
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertTrue($this->hasMatch($matches, $transacties[0], $bestellingen[0], PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, $transacties[1], $bestellingen[2], PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, $transacties[3], $bestellingen[3], PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, null, $bestellingen[1], PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));
		$this->assertTrue($this->hasMatch($matches, $transacties[2], null, PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING));
		$this->assertTrue($this->hasMatch($matches, null, $bestellingen[1], PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));
		$this->assertTrue($this->hasMatch($matches, $transacties[4], $bestellingen[4], PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG));

	}

	public function testMatchDifferentLength() {
		$transacties = [
			$this->trans(1, 100)
		];

		$bestellingen = [
			$this->best(100, 27), #missende transactie
			$this->best(101, 100),
			$this->best(105, 20) #missende transactie
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertTrue($this->hasMatch($matches, $transacties[0], $bestellingen[1], PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, null, $bestellingen[2], PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));
		$this->assertTrue($this->hasMatch($matches, null, $bestellingen[0], PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));

	}

	public function testMatchDifferentLength2() {
		$transacties = [
			$this->trans(0, 170), # missende bestelling
			$this->trans(1, 100),
			$this->trans(2, 200), # missende bestelling
		];

		$bestellingen = [
			$this->best(101, 100)
		];

		$matcher = $this->createMatcher();
		$matcher->setPinTransacties($transacties);
		$matcher->setPinBestellingen($bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertTrue($this->hasMatch($matches, $transacties[1], $bestellingen[0], PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, $transacties[0], null, PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING));
		$this->assertTrue($this->hasMatch($matches, $transacties[2], null, PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING));

	}

	private function trans($id, $bedrag) {
		$transactie = new PinTransactie();
		$transactie->id = $id;
		$transactie->amount = "EUR " . $bedrag;
		return $transactie;
	}

	private function best($id, $bedrag) {

		$bestellingInhoud = new CiviBestellingInhoud();
		$bestellingInhoud->aantal = $bedrag;
		$bestellingInhoud->bestelling_id = $id;
		$bestellingInhoud->product_id = CiviProductTypeEnum::PINTRANSACTIE;

		$bestelling = new CiviBestelling();
		$bestelling->inhoud->add($bestellingInhoud);

		return $bestelling;
	}

	/**
	 * @param $matches PinTransactieMatch[]
	 * @param $transactie
	 * @param $bestelling
	 * @param $status
	 * @return bool
	 */
	private function hasMatch($matches, $transactie, $bestelling, $status) {
		foreach ($matches as $match) {
			if ($match->transactie == $transactie && $match->bestelling == $bestelling && $match->status == $status)
				return true;
		}
		return false;
	}
}
