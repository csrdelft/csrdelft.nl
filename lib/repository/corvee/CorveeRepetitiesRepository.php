<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\AbstractRepository;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method CorveeRepetitie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeRepetitie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeRepetitie[]    findAll()
 * @method CorveeRepetitie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeRepetitiesRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registy)
	{
		parent::__construct($registy, CorveeRepetitie::class);
	}

	public function nieuw(MaaltijdRepetitie $maaltijdRepetitie = null): CorveeRepetitie
	{
		$repetitie = new CorveeRepetitie();
		$repetitie->crv_repetitie_id = null;
		$repetitie->maaltijdRepetitie = $maaltijdRepetitie;
		$repetitie->mlt_repetitie_id = $maaltijdRepetitie->mlt_repetitie_id ?? null;
		$repetitie->dag_vd_week = intval(
			InstellingUtil::instelling('corvee', 'standaard_repetitie_weekdag')
		);
		$repetitie->periode_in_dagen = intval(
			InstellingUtil::instelling('corvee', 'standaard_repetitie_periode')
		);
		$repetitie->corveeFunctie = null;
		$repetitie->standaard_punten = 0;
		$repetitie->standaard_aantal = intval(
			InstellingUtil::instelling('corvee', 'standaard_aantal_corveers')
		);
		$repetitie->voorkeurbaar = ((bool) InstellingUtil::instelling(
			'corvee',
			'standaard_voorkeurbaar'
		));

		return $repetitie;
	}

	public function getFirstOccurrence(CorveeRepetitie $repetitie): DateTimeImmutable|false
	{
		$datum = time();
		$shift = $repetitie->dag_vd_week - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date_create_immutable('@' . $datum);
	}

	/**
	 * @return CorveeRepetitie[]
	 */
	public function getVoorkeurbareRepetities(): array
	{
		$repetities = $this->findBy(['voorkeurbaar' => true]);
		$result = [];
		foreach ($repetities as $repetitie) {
			$result[$repetitie->crv_repetitie_id] = $repetitie;
		}
		return $result;
	}

	public function getAlleRepetities(): array
	{
		return $this->findAll();
	}

	/**
	 * Haalt de periodieke taken op die gekoppeld zijn aan een periodieke maaltijd.
	 *
	 * @param int $mrid
	 * @return CorveeRepetitie[]
	 */
	public function getRepetitiesVoorMaaltijdRepetitie($mrid): array
	{
		return $this->findBy(['mlt_repetitie_id' => $mrid]);
	}

	/**
	 * @param $crid
	 * @return CorveeRepetitie|null
	 */
	public function getRepetitie($crid): ?CorveeRepetitie
	{
		return $this->find($crid);
	}

	// Maaltijd-Repetitie-Corvee ############################################################

	/**
	 * Called when a MaaltijdRepetitie is going to be deleted.
	 *
	 * @param int $mrid
	 * @return bool
	 */
	public function existMaaltijdRepetitieCorvee($mrid): bool
	{
		return count($this->findBy(['mlt_repetitie_id' => $mrid])) > 0;
	}

	// Functie-Repetities ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 */
	public function existFunctieRepetities($fid): bool
	{
		return count($this->findBy(['functie_id' => $fid])) > 0;
	}
}
