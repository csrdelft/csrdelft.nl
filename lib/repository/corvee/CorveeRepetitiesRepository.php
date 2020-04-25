<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method CorveeRepetitie|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeRepetitie|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeRepetitie[]    findAll()
 * @method CorveeRepetitie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeRepetitiesRepository extends AbstractRepository {
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;

	public function __construct(ManagerRegistry $registy, CorveeTakenRepository $corveeTakenRepository) {
		parent::__construct($registy, CorveeRepetitie::class);
		$this->corveeTakenRepository = $corveeTakenRepository;
	}

	public function nieuw($crid = 0, $mrid = null, $dag = null, $periode = null, $fid = 0, $punten = 0, $aantal = null, $voorkeur = null) {
		$repetitie = new CorveeRepetitie();
		$repetitie->crv_repetitie_id = (int)$crid;
		$repetitie->mlt_repetitie_id = $mrid;
		if ($dag === null) {
			$dag = intval(instelling('corvee', 'standaard_repetitie_weekdag'));
		}
		$repetitie->dag_vd_week = $dag;
		if ($periode === null) {
			$periode = intval(instelling('corvee', 'standaard_repetitie_periode'));
		}
		$repetitie->periode_in_dagen = $periode;
		$repetitie->functie_id = $fid;
		$repetitie->standaard_punten = $punten;
		if ($aantal === null) {
			$aantal = intval(instelling('corvee', 'standaard_aantal_corveers'));
		}
		$repetitie->standaard_aantal = $aantal;
		if ($voorkeur === null) {
			$voorkeur = (boolean)instelling('corvee', 'standaard_voorkeurbaar');
		}
		$repetitie->voorkeurbaar = $voorkeur;

		return $repetitie;
	}

	public function getFirstOccurrence(CorveeRepetitie $repetitie) {
		$datum = time();
		$shift = $repetitie->dag_vd_week - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date('Y-m-d', $datum);
	}

	/**
	 * @return CorveeRepetitie[]
	 */
	public function getVoorkeurbareRepetities() {
		$repetities = $this->findBy(['voorkeurbaar' => true]);
		$result = [];
		foreach ($repetities as $repetitie) {
			$result[$repetitie->crv_repetitie_id] = $repetitie;
		}
		return $result;
	}

	public function getAlleRepetities() {
		return $this->findAll();
	}

	/**
	 * Haalt de periodieke taken op die gekoppeld zijn aan een periodieke maaltijd.
	 *
	 * @param int $mrid
	 * @return CorveeRepetitie[]
	 */
	public function getRepetitiesVoorMaaltijdRepetitie($mrid) {
		return $this->findBy(['mlt_repetitie_id' => $mrid]);
	}

	/**
	 * @param $crid
	 * @return CorveeRepetitie|null
	 */
	public function getRepetitie($crid) {
		return $this->find($crid);
	}

	public function saveRepetitie($crid, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
		return $this->_em->transactional(function () use ($crid, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
			$voorkeuren = 0;
			if ($crid == 0) {
				$repetitie = $this->nieuw(0, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur);
				$this->_em->persist($repetitie);
				$this->_em->flush();
			} else {
				$repetitie = $this->getRepetitie($crid);
				$repetitie->mlt_repetitie_id = $mrid;
				$repetitie->dag_vd_week = $dag;
				$repetitie->periode_in_dagen = $periode;
				$repetitie->functie_id = $fid;
				$repetitie->standaard_punten = $punten;
				$repetitie->standaard_aantal = $aantal;
				$repetitie->voorkeurbaar = (boolean)$voorkeur;
				$this->_em->persist($repetitie);
				$this->_em->flush();
				if (!$voorkeur) { // niet (meer) voorkeurbaar
					$voorkeuren = ContainerFacade::getContainer()->get(CorveeVoorkeurenRepository::class)->verwijderVoorkeuren($crid);
				}
			}
			return array($repetitie, $voorkeuren);
		});
	}

	public function verwijderRepetitie($crid) {
		if (!is_numeric($crid) || $crid <= 0) {
			throw new CsrGebruikerException('Verwijder corvee-repetitie faalt: Invalid $crid =' . $crid);
		}
		if ($this->corveeTakenRepository->existRepetitieTaken($crid)) {
			$this->corveeTakenRepository->verwijderRepetitieTaken($crid); // delete corveetaken first (foreign key)
			throw new CsrGebruikerException('Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}

		return $this->_em->transactional(function () use ($crid) {
			$aantal = ContainerFacade::getContainer()->get(CorveeVoorkeurenRepository::class)->verwijderVoorkeuren($crid); // delete voorkeuren first (foreign key)
			$repetitie = $this->find($crid);
			$this->_em->remove($repetitie);
			$this->_em->flush();
			return $aantal;
		});
	}

	// Maaltijd-Repetitie-Corvee ############################################################

	/**
	 * Called when a MaaltijdRepetitie is going to be deleted.
	 *
	 * @param int $mrid
	 * @return bool
	 */
	public function existMaaltijdRepetitieCorvee($mrid) {
		return count($this->findBy(['mlt_repetitie_id' => $mrid])) > 0;
	}

	// Functie-Repetities ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 */
	public function existFunctieRepetities($fid) {
		return count($this->findBy(['functie_id' => $fid])) > 0;
	}

}
