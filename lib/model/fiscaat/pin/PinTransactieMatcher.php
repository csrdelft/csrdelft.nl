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
class PinTransactieMatcher
{
	/**
	 * @param PinTransactie[] $pinTransacties
	 * @param CiviBestellingInhoud[] $pinBestellingen Pin bestellingen
	 * @return int[][]
	 * @throws CsrException
	 */
	public static function damerauLevenshteinMatrix(array $pinTransacties, array $pinBestellingen)
	{
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
				if ($pinTransacties[$i]->getBedragInCenten() == $pinBestellingen[$j]->aantal) {
					$cost = 0;
				} else {
					$cost = 1;
				}

				$distanceMatrix[$i + 1][$j + 1] = min($distanceMatrix[$i][$j + 1] + 1, // insert
					$distanceMatrix[$i + 1][$j] + 1, // delete
					$distanceMatrix[$i][$j] + $cost); // replace

				if ($i > 0 && $j > 0 && $pinTransacties[$i]->getBedragInCenten() == $pinBestellingen[$j - 1]->aantal && $pinTransacties[$i - 1]->getBedragInCenten() == $pinBestellingen[$j]->aantal) {
					$distanceMatrix[$i + 1][$j + 1] = min($distanceMatrix[$i + 1][$j + 1],
						$distanceMatrix[$i - 1][$j - 1] + $cost); // transposition
				}
			}
		}

		return $distanceMatrix;
	}

	/**
	 * @param PinTransactie[] $pinTransacties
	 * @param CiviBestellingInhoud[] $pinBestellingen
	 * @return PinTransactieMatch[]
	 * @throws CsrException
	 */
	public static function match(array $pinTransacties, array $pinBestellingen)
	{
		$i = count($pinTransacties);
		$j = count($pinBestellingen);
		$matches = [];

		if ($i === 0) {
			foreach ($pinBestellingen as $pinBestelling) {
				$matches[] = PinTransactieMatch::missendeTransactie($pinBestelling);
			}

			return $matches;
		} elseif ($j === 0) {
			foreach ($pinTransacties as $pinTransactie) {
				$matches[] = PinTransactieMatch::missendeBestelling($pinTransactie);
			}

			return $matches;
		}

		$distanceMatrix = static::damerauLevenshteinMatrix($pinTransacties, $pinBestellingen);

		while ($i != -1 && $j != -1) {
			if ($i > 1 && $j > 1 && $pinTransacties[$i - 1]->getBedragInCenten() == $pinBestellingen[$j - 2]->aantal && $pinTransacties[$i - 2]->getBedragInCenten() == $pinBestellingen[$j - 1]->aantal) {
				if ($distanceMatrix[$i - 2][$j - 2] < $distanceMatrix[$i][$j]) {
					$matches[] = PinTransactieMatch::omgedraaid($pinTransacties[$i - 1], $pinBestellingen[$j - 2]);
					$i -= 2;
					$j -= 2;
					continue;
				}
			}

			$isMatch = isset($distanceMatrix[$i - 1][$j - 1]) ? $distanceMatrix[$i - 1][$j - 1] : PHP_INT_MAX;
			$isMissendeBestelling = isset($distanceMatrix[$i - 1][$j]) ? $distanceMatrix[$i - 1][$j] : PHP_INT_MAX;
			$isMissendeTransactie = isset($distanceMatrix[$i][$j - 1]) ? $distanceMatrix[$i][$j - 1] : PHP_INT_MAX;

			$operationArray = [$isMatch, $isMissendeBestelling, $isMissendeTransactie];

			$index = array_keys($operationArray, min($operationArray))[0];

			if ($index === 0) {
				if (isset($distanceMatrix[$i - 1][$j - 1]) && $distanceMatrix[$i][$j] > $distanceMatrix[$i - 1][$j - 1]) {
					$matches[] = PinTransactieMatch::verkeerdBedrag($pinTransacties[$i - 1], $pinBestellingen[$j - 1]);
				} elseif ($i != 0 && $j != 0) {
					$matches[] = PinTransactieMatch::match($pinTransacties[$i - 1], $pinBestellingen[$j - 1]);
				}

				$i--;
				$j--;
			} elseif ($index === 1) {
				$matches[] = PinTransactieMatch::missendeBestelling($pinTransacties[$i - 1]);
				$j--;
			} elseif ($index === 2) {
				$matches[] = PinTransactieMatch::missendeTransactie($pinBestellingen[$j - 1]);
				$i--;
			}
		}

		while ($i != -1) {
			$matches[] = PinTransactieMatch::missendeTransactie($pinBestellingen[$i]);
			$i--;
		}

		while ($j != -1) {
			$matches[] = PinTransactieMatch::missendeBestelling($pinTransacties[$j]);
			$j--;
		}

		return $matches;
	}

	/**
	 * @param PinTransactieMatch[] $matches
	 * @return string
	 * @throws CsrException
	 */
	public static function genereerReport($matches)
	{
		ob_start();

		$verschil = 0;

		foreach ($matches as $match) {

			switch ($match->reden) {
				case PinTransactieMatchStatusEnum::REASON_MISSENDE_BESTELLING:
					$pinTransactie = PinTransactieModel::get($match->transactie_id);
					$verschil += $pinTransactie->getBedragInCenten();
					$moment = date('H:m:s' , strtotime($pinTransactie->datetime));

					printf("%s - Missende bestelling voor pintransactie %d om %s van %s.\n", $moment, $pinTransactie->STAN, $pinTransactie->datetime, $pinTransactie->amount);
					break;
				case PinTransactieMatchStatusEnum::REASON_MISSENDE_TRANSACTIE:
					$pinBestelling = CiviBestellingModel::get($match->bestelling_id);
					$pinBestellingInhoud = CiviBestellingInhoudModel::instance()->getAll($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE)->fetch();
					$verschil -= $pinBestellingInhoud->aantal;
					$moment = date('H:m:s' , strtotime($pinBestelling->moment));

					printf("%s - Missende transactie voor bestelling %d om %s van EUR %.2f door %d.\n", $moment, $pinBestelling->id, $pinBestelling->moment, $pinBestellingInhoud->aantal / 100, $pinBestelling->uid);
					break;
				case PinTransactieMatchStatusEnum::REASON_TRANSPOSE:
					printf("Twee bestellingen zijn omgedraaid.\n");
					break;
				case PinTransactieMatchStatusEnum::REASON_VERKEERD_BEDRAG:
					$pinTransactie = PinTransactieModel::get($match->transactie_id);
					$pinBestelling = CiviBestellingModel::get($match->bestelling_id);

					$pinBestellingInhoud = CiviBestellingInhoudModel::instance()->getAll($match->bestelling_id, CiviProductTypeEnum::PINTRANSACTIE)->fetch();

					$verschil += $pinTransactie->getBedragInCenten() - $pinBestellingInhoud->aantal;
					$moment = date('H:m:s', strtotime($pinTransactie->datetime));

					printf("%s - Bestelling en transactie hebben geen overeenkomend bedrag.\n", $moment);
					printf(" - %s Transactie %d om %s.\n", $pinTransactie->amount, $pinTransactie->STAN, $pinTransactie->datetime);
					printf(" - EUR %.2f Bestelling %d om %s door %d.\n", $pinBestellingInhoud->aantal / 100, $pinBestelling->id, $pinBestelling->moment, $pinBestelling->uid);
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
}
