<?php

namespace CsrDelft\service\pin;

use CsrDelft\common\CsrException;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\enum\CiviProductTypeEnum;
use CsrDelft\entity\pin\PinTransactie;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\entity\pin\PinTransactieMatchStatusEnum;
use CsrDelft\repository\pin\PinTransactieMatchRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Match transacties (wat in het systeem van de payment provider staat) met bestellingen (wat er in het SocCie systeem staat).
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20/02/2018
 */
class PinTransactieMatcher
{
	/**
	 * Constants.
	 */
	const COST_VERKEERD_BEDRAG = 3;
	const COST_MISSING = 2;

	/**
	 * @var PinTransactie[]
	 */
	private $pinTransacties;

	/**
	 * @var CiviBestelling[]
	 */
	private $pinBestellingen;

	/**
	 * @var PinTransactieMatch[]
	 */
	private $matches;

	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly PinTransactieMatchRepository $pinTransactieMatchModel
	) {
	}

	public function setPinTransacties(array $pinTransacties)
	{
		$this->pinTransacties = $pinTransacties;
	}

	public function setPinBestellingen(array $pinBestellingen)
	{
		$this->pinBestellingen = $pinBestellingen;
	}

	/**
	 */
	public function clean()
	{
		$ids = array_map(
			fn(CiviBestelling $inhoud) => $inhoud->id,
			$this->pinBestellingen
		);
		$this->pinTransactieMatchModel->cleanByBestellingIds($ids);
	}

	/**
	 * @param PinTransactie[] $pinTransacties
	 * @param CiviBestelling[] $pinBestellingen Pin bestellingen
	 * @return int[][]
	 * @throws CsrException
	 */
	protected function levenshteinMatrix(
		array $pinTransacties,
		array $pinBestellingen
	) {
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

	private function compareDate(
		?DateTimeInterface $a,
		?DateTimeInterface $b
	): int {
		if (!$a instanceof DateTimeInterface && !$b instanceof DateTimeInterface) {
			return 0;
		} elseif (!$a instanceof DateTimeInterface) {
			return 1;
		} elseif (!$b instanceof DateTimeInterface) {
			return -1;
		}
		return $b->getTimestamp() - $a->getTimestamp();
	}

	/**
	 * @throws CsrException
	 */
	public function match()
	{
		// Sorteer beide op volgorde van moment
		usort(
			$this->pinBestellingen,
			fn(CiviBestelling $a, CiviBestelling $b) => $this->compareDate(
				$a->moment,
				$b->moment
			)
		);
		usort(
			$this->pinTransacties,
			fn(PinTransactie $a, PinTransactie $b) => $this->compareDate(
				$a->datetime,
				$b->datetime
			)
		);

		$pinTransacties = $this->pinTransacties;
		$pinBestellingen = $this->pinBestellingen;
		$distanceMatrix = $this->levenshteinMatrix(
			$pinTransacties,
			$pinBestellingen
		);

		$matches = [];
		$indexTransactie = count($pinTransacties) - 1;
		$indexBestelling = count($pinBestellingen) - 1;

		while ($indexTransactie >= 0 && $indexBestelling >= 0) {
			$matchCost = $this->matchCost($indexTransactie, $indexBestelling);
			$matchDistance =
				$distanceMatrix[$indexTransactie][$indexBestelling] + $matchCost;
			$missendeBestellingDistance =
				$distanceMatrix[$indexTransactie][$indexBestelling + 1] +
				self::COST_MISSING;
			$missendeTransactieDistance =
				$distanceMatrix[$indexTransactie + 1][$indexBestelling] +
				self::COST_MISSING;

			$distance = $distanceMatrix[$indexTransactie + 1][$indexBestelling + 1];

			switch ($distance) {
				case $matchDistance:
					if ($matchCost > 0) {
						// Maak geen matches meer met verkeerde bedragen: moeten handmatig opgelost worden
						$matches[] = PinTransactieMatch::missendeTransactie(
							$pinBestellingen[$indexBestelling]
						);
						$matches[] = PinTransactieMatch::missendeBestelling(
							$pinTransacties[$indexTransactie]
						);
					} else {
						$matches[] = PinTransactieMatch::match(
							$pinTransacties[$indexTransactie],
							$pinBestellingen[$indexBestelling]
						);
					}

					$indexTransactie--;
					$indexBestelling--;

					break;

				case $missendeTransactieDistance:
					$matches[] = PinTransactieMatch::missendeTransactie(
						$pinBestellingen[$indexBestelling]
					);
					$indexBestelling--;

					break;

				case $missendeBestellingDistance:
					$matches[] = PinTransactieMatch::missendeBestelling(
						$pinTransacties[$indexTransactie]
					);
					$indexTransactie--;

					break;
			}
		}

		while ($indexTransactie >= 0) {
			$matches[] = PinTransactieMatch::missendeBestelling(
				$pinTransacties[$indexTransactie]
			);
			$indexTransactie--;
		}

		while ($indexBestelling >= 0) {
			$matches[] = PinTransactieMatch::missendeTransactie(
				$pinBestellingen[$indexBestelling]
			);
			$indexBestelling--;
		}

		$this->matches = array_reverse($matches);
	}

	/**
	 * @return bool
	 */
	public function bevatFouten()
	{
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
	public function genereerReport()
	{
		ob_start();

		$verschil = 0;

		foreach ($this->matches as $match) {
			switch ($match->status) {
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_BESTELLING:
					$pinTransactie = $match->transactie;
					$verschil += $pinTransactie->getBedragInCenten();
					$moment = DateUtil::dateFormatIntl(
						$pinTransactie->datetime,
						DateUtil::DATETIME_FORMAT
					);

					printf(
						"%s - Missende bestelling voor pintransactie %d om %s van %s.\n",
						$moment,
						$pinTransactie->STAN,
						$moment,
						$pinTransactie->amount
					);
					break;
				case PinTransactieMatchStatusEnum::STATUS_MISSENDE_TRANSACTIE:
					$pinBestelling = $match->bestelling;
					$pinBestellingInhoud = $pinBestelling->getProduct(
						CiviProductTypeEnum::PINTRANSACTIE
					);
					$verschil -= $pinBestellingInhoud->aantal;
					$moment = DateUtil::dateFormatIntl(
						$pinBestelling->moment,
						DateUtil::DATETIME_FORMAT
					);

					printf(
						"%s - Missende transactie voor bestelling %d om %s van EUR %.2f door %d.\n",
						$moment,
						$pinBestelling->id,
						$moment,
						$pinBestellingInhoud->aantal / 100,
						$pinBestelling->uid
					);
					break;
				case PinTransactieMatchStatusEnum::STATUS_VERKEERD_BEDRAG:
					$pinTransactie = $match->transactie;
					$pinBestelling = $match->bestelling;
					$pinBestellingInhoud = $pinBestelling->getProduct(
						CiviProductTypeEnum::PINTRANSACTIE
					);

					$verschil +=
						$pinTransactie->getBedragInCenten() - $pinBestellingInhoud->aantal;
					$moment = DateUtil::dateFormatIntl(
						$pinTransactie->datetime,
						DateUtil::DATETIME_FORMAT
					);

					printf(
						"%s - Bestelling en transactie hebben geen overeenkomend bedrag.\n",
						$moment
					);
					printf(
						" - %s Transactie %d om %s.\n",
						$pinTransactie->amount,
						$pinTransactie->STAN,
						DateUtil::dateFormatIntl(
							$pinTransactie->datetime,
							DateUtil::DATETIME_FORMAT
						)
					);
					printf(
						" - EUR %.2f Bestelling %d om %s door %s.\n",
						$pinBestellingInhoud->aantal / 100,
						$pinBestelling->id,
						DateUtil::dateFormatIntl(
							$pinBestelling->moment,
							DateUtil::DATETIME_FORMAT
						),
						$pinBestelling->uid
					);
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
	public function save()
	{
		foreach ($this->matches as $match) {
			$this->entityManager->persist($match);
		}
		$this->entityManager->flush();
	}

	/**
	 * @return PinTransactieMatch[]
	 */
	public function getMatches()
	{
		return $this->matches;
	}

	private function matchCost($i, $j)
	{
		if (
			$this->pinTransacties[$i]->getBedragInCenten() ==
			$this->pinBestellingen[$j]->getProduct(CiviProductTypeEnum::PINTRANSACTIE)
				->aantal
		) {
			return 0;
		} else {
			return self::COST_VERKEERD_BEDRAG;
		}
	}
}
