<?php
declare(strict_types=1);

use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\CiviProductTypeEnum;
use CsrDelft\model\entity\fiscaat\pin\PinTransactie;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\fiscaat\pin\PinTransactieMatcher;
use PHPUnit\Framework\TestCase;

final class PinTransactieMatcherTest extends TestCase
{
    public function testMatch(): void
    {
    	$transacties = [
    		$this->trans(0, 100),
			$this->trans(2, 236),
			$this->trans(3, 42), #missende bestelling
			$this->trans(4, 1115),
			$this->trans(5, 16) #verkeerd bedrag
		];

		$bestellingen = [
			$this->best(100,100),
			$this->best(101,14), # missende transactie (en B'vo)
			$this->best(102,236),
			$this->best(104,1115),
			$this->best(105,20) #verkeerd bedrag
		];

    	$matcher = new PinTransactieMatcher($transacties, $bestellingen);
    	$matcher->match();
    	$matches = $matcher->getMatches();
    	$this->assertTrue($this->hasMatch($matches, 0, 100, PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, 2, 102, PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, 4, 104, PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, null, 101, PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));
		$this->assertTrue($this->hasMatch($matches, 3, null, PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING));
		$this->assertTrue($this->hasMatch($matches, null, 101, PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));
		$this->assertTrue($this->hasMatch($matches, 5, 105, PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG));

    }

	public function testMatchDifferentLength(): void
	{
		$transacties = [
			$this->trans(0, 100)
		];

		$bestellingen = [
			$this->best(100,100),
			$this->best(105,20) #missende transactie
		];

		$matcher = new PinTransactieMatcher($transacties, $bestellingen);
		$matcher->match();
		$matches = $matcher->getMatches();
		$this->assertTrue($this->hasMatch($matches, 0, 100, PinTransactieMatchStatusEnum::STATUS_MATCH));
		$this->assertTrue($this->hasMatch($matches, null, 105, PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE));

	}

    private function trans($id, $bedrag) {
		$transactie = new PinTransactie();
		$transactie->id = $id;
		$transactie->amount = "EUR ".$bedrag;
		return $transactie;
	}

	private function best($id, $bedrag) {
    	$bestelling = new CiviBestellingInhoud();
    	$bestelling->aantal = $bedrag;
    	$bestelling->bestelling_id = $id;
    	$bestelling->product_id = CiviProductTypeEnum::PINTRANSACTIE;
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
			if ($match->transactie_id == $transactie && $match->bestelling_id == $bestelling && $match->status == $status)
				return true;
		}
		return false;
	}
}
