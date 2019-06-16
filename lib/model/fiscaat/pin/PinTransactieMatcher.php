<?php

namespace CsrDelft\model\fiscaat\pin;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\fiscaat\CiviProductTypeEnum;
use CsrDelft\model\entity\fiscaat\pin\PinTransactie;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatch;
use CsrDelft\model\entity\fiscaat\pin\PinTransactieMatchStatusEnum;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\model\fiscaat\CiviBestellingModel;

/**
 * Match transacties (wat in het systeem van de payment provider staat) met bestellingen (wat er in het SocCie systeem staat).
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20/02/2018
 */
class PinTransactieMatcher {
	/**
	 * Constants.
	 */
	const COST_VERKEERD_BEDRAG = 3;
	const COST_MISSING = 2;
	const TIME_FORMAT = 'H:m:s';

	/**
	 * @var PinTransactie[]
	 */
	private $pinTransacties;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	private $pinBestellingen;

	/**
	 * @var PinTransactieMatch[]
	 */
	private $matches;

	/**
	 * @param PinTransactie[] $pinTransacties
	 * @param CiviBestellingInhoud[] $pinBestellingen
	 */
	public function __construct(array $pinTransacties, array $pinBestellingen) {
		$this->pinTransacties = $pinTransacties;
		$this->pinBestellingen = $pinBestellingen;
	}

	/**
	 */
	public function clean() {
		foreach ($this->pinBestellingen as $pinBestelling) {
			$matches = PinTransactieMatchModel::instance()->find('bestelling_id = ?', [$pinBestelling->bestelling_id])->fetchAll();

			foreach ($matches as $match) {
				PinTransactieMatchModel::instance()->delete($match);
			}
		}
	}

	/**
	 * @param PinTransactie[] $pinTransacties
	 * @param CiviBestellingInhoud[] $pinBestellingen Pin bestellingen
	 * @return int[][]
	 * @throws CsrException
	 */
	protected function levenshteinMatrix(array $pinTransacties, array $pinBestellingen) {
		$pinTransactiesCount = count($pinTransacties);
		$pinBestellingenCount = count($pinBestellingen);

		$distanceMatrix = [];

		for ($i = 0; $i <= $pinTransactiesCount; $i++) {
			$distanceMatrix[$i] = array_fill(0, $pinBestellingenCount + 1, 0);
			$distanceMatrix[$i][0] = $i;
		}

		for ($j = 0; $j <= $pinBestellingenCount; $j++) {
			$distanceMatrix[0][$j] = $j;
		}

		for ($i = 0; $i < $pinTransactiesCount; $i++) {
			for ($j = 0; $j < $pinBestellingenCount; $j++) {
				$cost = $this->matchCost($i, $j);

				$distanceMatrix[$i + 1][$j + 1] = min(
					$distanceMatrix[$i][$j + 1] + self::COST_MISSING, // insert
					$distanceMatrix[$i + 1][$j] + self::COST_MISSING, // delete
					$distanceMatrix[$i][$j] + $cost // replace
				);
			}
		}

		return $distanceMatrix;
	}

	/**
	 * @throws CsrException
	 */
	public function match() {
		$pinTransacties = $this->pinTransacties;
		$pinBestellingen = $this->pinBestellingen;
		$distanceMatrix = $this->levenshteinMatrix($pinTransacties, $pinBestellingen);

		$matches = [];
		$indexTransactie = count($pinTransacties) - 1;
		$indexBestelling = count($pinBestellingen) - 1;

		while ($indexTransactie >= 0 && $indexBestelling >=0) {
			$matchCost = $this->matchCost($indexTransactie, $indexBestelling);
			$matchDistance = $distanceMatrix[$indexTransactie][$indexBestelling] + $matchCost;
			$missendeBestellingDistance = $distanceMatrix[$indexTransactie][$indexBestelling +1] + self::COST_MISSING;
			$missendeTransactieDistance = $distanceMatrix[$indexTransactie + 1][$indexBestelling ] + self::COST_MISSING;

			$distance = $distanceMatrix[$indexTransactie+1][$indexBestelling+1];

			switch ($distance) {
				case $matchDistance:
					if ($matchCost > 0) {
						$matches[] = PinTransactieMatch::verkeerdBedrag($pinTransacties[$indexTransactie], $pinBestellingen[$indexBestelling]);
					} else {
						$matches[] = PinTransactieMatch::match($pinTransacties[$indexTransactie], $pinBestellingen[$indexBestelling]);
					}

					$indexTransactie--;
					$indexBestelling--;

					break;

				case $missendeTransactieDistance:
					$matches[] = PinTransactieMatch::missendeTransactie($pinBestellingen[$indexBestelling]);
					$indexBestelling--;

					break;

				case $missendeBestellingDistance:
					$matches[] = PinTransactieMatch::missendeBestelling($pinTransacties[$indexTransactie]);
					$indexTransactie--;

					break;
			}
		}

		while ($indexTransactie >= 0) {
			$matches[] = PinTransactieMatch::missendeTransactie($pinBestellingen[$indexTransactie]);
			$indexTransactie--;
		}

		while ($indexBestelling >= 0) {
			$matches[] = PinTransactieMatch::missendeBestelling($pinTransacties[$indexBestelling]);
			$indexBestelling--;
		}

		$this->matches = array_reverse($matches);
	}

	/**
	 * @return bool
	 */
	public function bevatFouten() {
		foreach ($this->matches as $match) {
			if ($match->status !== PinTransactieMatchStatusEnum::STATUS_MATCH) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 * @throws CsrException
	 */
	public function genereerReport() {
		ob_start();

		$verschil = 0;

		foreach ($this->matches as $match) {

			switch ($match->status) {
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING:
					$pinTransactie = PinTransactieModel::get($match->transactie_id);
					$verschil += $pinTransactie->getBedragInCenten();
					$moment = date(self::TIME_FORMAT, strtotime($pinTransactie->datetime));

					printf("%s - Missende bestelling voor pintransactie %d om %s van %s.\n", $moment, $pinTransactie->STAN, $pinTransactie->datetime, $pinTransactie->amount);
					break;
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE:
					$pinBestelling = CiviBestellingModel::get($match->bestelling_id);
					$pinBestellingInhoud = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);
					$verschil -= $pinBestellingInhoud->aantal;
					$moment = date(self::TIME_FORMAT, strtotime($pinBestelling->moment));

					printf("%s - Missende transactie voor bestelling %d om %s van EUR %.2f door %d.\n", $moment, $pinBestelling->id, $pinBestelling->moment, $pinBestellingInhoud->aantal / 100, $pinBestelling->uid);
					break;
				case PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG:
					$pinTransactie = PinTransactieModel::get($match->transactie_id);
					$pinBestelling = CiviBestellingModel::get($match->bestelling_id);

					$pinBestellingInhoud = CiviBestellingInhoudModel::instance()->getVoorBestellingEnProduct($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE);

					$verschil += $pinTransactie->getBedragInCenten() - $pinBestellingInhoud->aantal;
					$moment = date(self::TIME_FORMAT, strtotime($pinTransactie->datetime));

					printf("%s - Bestelling en transactie hebben geen overeenkomend bedrag.\n", $moment);
					printf(" - %s Transactie %d om %s.\n", $pinTransactie->amount, $pinTransactie->STAN, $pinTransactie->datetime);
					printf(" - EUR %.2f Bestelling %d om %s door %s.\n", $pinBestellingInhoud->aantal / 100, $pinBestelling->id, $pinBestelling->moment, $pinBestelling->uid);
					break;
				default:
					// Er is niets mis gegaan.
					break;
			}
		}

		printf("Verschil is: EUR %.2f.\n", $verschil / 100);

		$report = ob_get_contents();

		ob_end_clean();

		return $report;
	}

	/**
	 */
	public function save() {
		foreach ($this->matches as $match) {
			PinTransactieMatchModel::instance()->create($match);
		}
	}

	/**
	 * @return PinTransactieMatch[]
	 */
	public function getMatches() {
		return $this->matches;
	}

	private function matchCost($i, $j) {
		if ($this->pinTransacties[$i]->getBedragInCenten() == $this->pinBestellingen[$j]->aantal) {
			return 0;
		} else {
			return self::COST_VERKEERD_BEDRAG;
		}
	}
}
