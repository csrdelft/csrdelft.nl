<?php

namespace CsrDelft\repository\corvee;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\RepetitieTakenUpdateDTO;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\corvee\CorveePuntenService;
use CsrDelft\service\security\LoginService;
use DateInterval;
use DateTimeInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use PDOStatement;
use Throwable;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method CorveeTaak|null find($id, $lockMode = null, $lockVersion = null)
 * @method CorveeTaak|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorveeTaak[]    findAll()
 * @method CorveeTaak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorveeTakenRepository extends AbstractRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, CorveeTaak::class);
	}

	/**
	 * @param CorveeTaak $taak
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function updateGemaild(CorveeTaak $taak) {
		$taak->setWanneerGemaild(date_format_intl(date_create(), DATETIME_FORMAT));
		$this->_em->persist($taak);
		$this->_em->flush();
	}

	/**
	 * @param CorveeTaak $taak
	 * @param $uid
	 * @return bool
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function taakToewijzenAanLid(CorveeTaak $taak, $uid) {
		if ($taak->uid === $uid) {
			return false;
		}
		$puntenruilen = false;
		if ($taak->wanneer_toegekend !== null) {
			$puntenruilen = true;
		}
		$taak->wanneer_gemaild = '';
		if ($puntenruilen && $taak->uid !== null) {
			$this->puntenIntrekken($taak);
		}
		$taak->setUid($uid);
		if ($puntenruilen && $uid !== null) {
			$this->puntenToekennen($taak);
		} else {
			$this->_em->persist($taak);
			$this->_em->flush();
		}
		return true;
	}

	/**
	 * @param CorveeTaak $taak
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function puntenToekennen(CorveeTaak $taak) {
		ContainerFacade::getContainer()->get(CorveePuntenService::class)->puntenToekennen($taak->uid, $taak->punten, $taak->bonus_malus);
		$taak->punten_toegekend = $taak->punten_toegekend + $taak->punten;
		$taak->bonus_toegekend = $taak->bonus_toegekend + $taak->bonus_malus;
		$taak->wanneer_toegekend = date_create_immutable();
		$this->_em->persist($taak);
		$this->_em->flush();
	}

	/**
	 * @param CorveeTaak $taak
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function puntenIntrekken(CorveeTaak $taak) {
		ContainerFacade::getContainer()->get(CorveePuntenService::class)->puntenIntrekken($taak->uid, $taak->punten, $taak->bonus_malus);
		$taak->punten_toegekend = $taak->punten_toegekend - $taak->punten;
		$taak->bonus_toegekend = $taak->bonus_toegekend - $taak->bonus_malus;
		$taak->wanneer_toegekend = null;
		$this->_em->persist($taak);
		$this->_em->flush();
	}

	/**
	 * @param array $taken
	 * @return array
	 */
	public function getRoosterMatrix(array $taken) {
		$matrix = array();
		foreach ($taken as $taak) {
			$datum = strtotime($taak->datum);
			$week = date('W', $datum);
			$matrix[$week][$datum][$taak->functie_id][] = $taak;
		}
		return $matrix;
	}

	/**
	 * @return CorveeTaak[]
	 */
	public function getKomendeTaken() {
		return $this->createQueryBuilder('ct')
			->where('ct.verwijderd = false and ct.datum >= :datum')
			->setParameter('datum', date_create())
			->orderBy('ct.datum', 'ASC')
			->getQuery()->getResult();
	}

	/**
	 * @return CorveeTaak[]
	 */
	public function getVerledenTaken() {
		return $this->createQueryBuilder('ct')
			->where('ct.verwijderd = false and ct.datum < :datum')
			->setParameter('datum', date_create())
			->orderBy('ct.datum', 'ASC')
			->getQuery()->getResult();
	}

	public function getAlleTaken($groupByUid = false) {
		$taken = $this->findBy(['verwijderd' => false], ['datum' => 'ASC']);
		if ($groupByUid) {
			$takenByUid = array();
			foreach ($taken as $taak) {
				$uid = $taak->uid;
				if ($uid !== null) {
					$takenByUid[$uid][] = $taak;
				}
			}
			return $takenByUid;
		}
		return $taken;
	}

	public function getVerwijderdeTaken() {
		return $this->findBy(['verwijderd' => true], ['datum' => 'ASC']);

	}

	public function getTaak($tid) {
		$taak = $this->find($tid);

		/** @var CorveeTaak $taak */
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}
		return $taak;
	}

	/**
	 * Haalt de taken op voor het ingelode lid of alle leden tussen de opgegeven data.
	 *
	 * @param DateTimeInterface $van Timestamp
	 * @param DateTimeInterface $tot Timestamp
	 * @param bool $iedereen
	 * @return CorveeTaak[]
	 * @throws CsrException
	 */
	public function getTakenVoorAgenda(DateTimeInterface $van, DateTimeInterface $tot, $iedereen = false) {
		$qb = $this->createQueryBuilder('ct');
		$qb->where('ct.verwijderd = false and ct.datum >= :van_datum and ct.datum <= :tot_datum');
		$qb->setParameter('van_datum', $van);
		$qb->setParameter('tot_datum', $tot);
		if (!$iedereen) {
			$qb->andWhere('ct.uid = :uid');
			$qb->setParameter('uid', LoginService::getUid());
		}
		return $qb->getQuery()->getResult();
	}

	/**
	 * Haalt de taken op voor een lid.
	 *
	 * @param string $uid
	 * @return PDOStatement|CorveeTaak[]
	 */
	public function getTakenVoorLid($uid) {
		return $this->findBy(['verwijderd' => false, 'uid' => $uid], ['datum' => 'ASC']);
	}

	/**
	 * Zoekt de laatste taak op van een lid.
	 *
	 * @param string $uid
	 * @return CorveeTaak
	 */
	public function getLaatsteTaakVanLid($uid) {
		return $this->findOneBy(['verwijderd' => false, 'uid' => $uid], ['datum' => 'DESC']);
	}

	/**
	 * Haalt de komende taken op waarvoor een lid is ingedeeld.
	 *
	 * @param string $uid
	 * @return CorveeTaak[]
	 */
	public function getKomendeTakenVoorLid($uid) {
		return $this->createQueryBuilder('ct')
			->where('ct.verwijderd = false and ct.uid = :uid and ct.datum >= :datum')
			->setParameter('uid', $uid)
			->setParameter('datum', date_create_immutable())
			->orderBy('ct.datum', 'ASC')
			->getQuery()->getResult();
	}

	/**
	 * @param $tid
	 * @param $fid
	 * @param $uid
	 * @param $crid
	 * @param $mid
	 * @param $datum
	 * @param $punten
	 * @param $bonus_malus
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function saveTaak($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		return $this->_em->transactional(function () use ($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
			if ($tid === 0) {
				$taak = $this->newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus);
			} else {
				$taak = $this->getTaak($tid);
				if ($taak->functie_id !== $fid) {
					$taak->crv_repetitie_id = null;
					$taak->functie_id = $fid;
				}
				$taak->maaltijd_id = $mid;
				$taak->datum = $datum;
				$taak->punten = $punten;
				$taak->bonus_malus = $bonus_malus;
				if (!$this->taakToewijzenAanLid($taak, $uid)) {
					$this->_em->persist($taak);
					$this->_em->flush();
				}
			}

			return $taak;
		});
	}

	/**
	 * @param $tid
	 * @return CorveeTaak|null
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function herstelTaak($tid) {
		$taak = $this->find($tid);
		if (!$taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is niet verwijderd');
		}
		$taak->verwijderd = false;
		$this->_em->persist($taak);
		$this->_em->flush();
		return $taak;
	}

	/**
	 * @return int
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function prullenbakLeegmaken() {
		$taken = $this->findBy(['verwijderd' => true]);
		foreach ($taken as $taak) {
			$this->_em->remove($taak);
		}
		$this->_em->flush();
		return count($taken);
	}

	/**
	 * @return int
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderOudeTaken() {
		/** @var CorveeTaak[] $taken */
		$taken = $this->createQueryBuilder('ct')
			->where('ct.datum < :datum')
			->setParameter('datum', date_create_immutable())
			->getQuery()->getResult();
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->_em->persist($taak);
		}
		$this->_em->flush();
		return count($taken);
	}

	/**
	 * @param $uid
	 * @return mixed
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderTakenVoorLid($uid) {
		/** @var CorveeTaak[] $taken */
		$taken = $this->createQueryBuilder('ct')
		->where('ct.uid = :uid and ct.datum >= :datum')
		->setParameter('uid', $uid)
			->setParameter('datum', date_create_immutable())
			->getQuery()->getResult();
		foreach ($taken as $taak) {
			$this->_em->remove($taak);
		}
		$this->_em->flush();
		return count($taken);
	}

	/**
	 * @param $tid
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderTaak($tid) {
		$taak = $this->find($tid);
		if ($taak->verwijderd) {
			$this->_em->remove($taak);
		} else {
			$taak->verwijderd = true;
			$this->_em->persist($taak);
		}
		$this->_em->flush();
	}

	public function vanRepetitie(CorveeRepetitie $repetitie, $datum, $mid = null, $uid = null, $bonus_malus = 0) {
		$taak = new CorveeTaak();
		$taak->taak_id = null;
		$taak->functie_id = $repetitie->corveeFunctie->functie_id;
		$taak->uid = $uid;
		$taak->crv_repetitie_id = $repetitie->crv_repetitie_id;
		$taak->maaltijd_id = $mid;
		$taak->datum = $datum;
		$taak->bonus_malus = $bonus_malus;
		$taak->punten = $repetitie->standaard_punten;
		$taak->punten_toegekend = 0;
		$taak->bonus_toegekend = 0;
		$taak->wanneer_toegekend = null;
		$taak->wanneer_gemaild = '';
		$taak->verwijderd = false;
		return $taak;

	}

	/**
	 * @param $fid
	 * @param $uid
	 * @param $crid
	 * @param $mid
	 * @param $datum
	 * @param $punten
	 * @param $bonus_malus
	 * @return CorveeTaak
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	private function newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		$taak = new CorveeTaak();
		$taak->taak_id = null;
		$taak->functie_id = $fid;
		$taak->setUid($uid);
		$taak->crv_repetitie_id = $crid;
		$taak->maaltijd_id = $mid;
		$taak->datum = $datum;
		$taak->punten = $punten;
		$taak->bonus_malus = $bonus_malus;
		$taak->punten_toegekend = 0;
		$taak->bonus_toegekend = 0;
		$taak->wanneer_toegekend = null;
		$taak->wanneer_gemaild = '';
		$taak->verwijderd = false;

		$this->_em->persist($taak);
		$this->_em->flush();

		return $taak;
	}

	// Maaltijd-Corvee ############################################################

	/**
	 * Haalt de taken op die gekoppeld zijn aan een maaltijd.
	 * Eventueel ook alle verwijderde taken.
	 *
	 * @param int $mid
	 * @param bool $verwijderd
	 * @return PDOStatement|CorveeTaak[]
	 * @throws CsrGebruikerException
	 */
	public function getTakenVoorMaaltijd($mid, $verwijderd = false) {
		if ($mid <= 0) {
			throw new CsrGebruikerException('Load taken voor maaltijd faalt: Invalid $mid =' . $mid);
		}
		if ($verwijderd) {
			return $this->findBy(['maaltijd_id' => $mid], ['datum' => 'ASC']);
		}
		return $this->findBy(['verwijderd' => false, 'maaltijd_id' => $mid], ['datum' => 'ASC']);
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return bool
	 */
	public function existMaaltijdCorvee($mid) {
		return count($this->findBy(['maaltijd_id' => $mid])) > 0;
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return int
	 * @throws ORMException
	 */
	public function verwijderMaaltijdCorvee($mid) {
		$taken = $this->findBy(['maaltijd_id' => $mid]);
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->_em->persist($taak);
		}
		$this->_em->flush();
		return count($taken);
	}

	// Functie-Taken ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 */
	public function existFunctieTaken($fid) {
		return count($this->findBy(['functie_id' => $fid])) > 0;
	}

	// Repetitie-Taken ############################################################

	/**
	 * @param CorveeRepetitie $repetitie
	 * @param $beginDatum
	 * @param $eindDatum
	 * @param null $mid
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function maakRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		if ($repetitie->periode_in_dagen < 1) {
			throw new CsrGebruikerException('New repetitie-taken faalt: $periode =' . $repetitie->periode_in_dagen);
		}

		return $this->_em->transactional(function () use ($repetitie, $beginDatum, $eindDatum, $mid) {
			return $this->newRepetitieTaken($repetitie, strtotime($beginDatum), strtotime($eindDatum), $mid);
		});
	}

	/**
	 * @param CorveeRepetitie $repetitie
	 * @param $beginDatum
	 * @param $eindDatum
	 * @param null $mid
	 * @return array
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function newRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		// start at first occurence
		$shift = $repetitie->dag_vd_week - date('w', $beginDatum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$beginDatum = strtotime('+' . $shift . ' days', $beginDatum);
		}
		$datum = $beginDatum;
		$taken = array();
		while ($datum <= $eindDatum) { // break after one
			for ($i = $repetitie->standaard_aantal; $i > 0; $i--) {
				$taak = $this->vanRepetitie($repetitie, date_create_immutable("@$datum"), $mid, null, 0);
				$this->_em->persist($taak);
				$taken[] = $taak;
			}
			if ($repetitie->periode_in_dagen < 1) {
				break;
			}
			$datum = strtotime('+' . $repetitie->periode_in_dagen . ' days', $datum);
		}

		$this->_em->flush();

		return $taken;
	}

	/**
	 * @param $crid
	 * @return int
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderRepetitieTaken($crid) {
		$taken = $this->findBy(['crv_repetitie_id' => $crid]);
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->_em->persist($taak);
		}

		$this->_em->flush();

		return count($taken);
	}

	/**
	 * Called when a CorveeRepetitie is updated or is going to be deleted.
	 *
	 * @param int $crid
	 * @return bool
	 */
	public function existRepetitieTaken($crid) {
		return count($this->findBy(['crv_repetitie_id' => $crid])) > 0;
	}

	/**
	 * @param CorveeRepetitie $repetitie
	 * @param $verplaats
	 * @return RepetitieTakenUpdateDTO
	 * @throws Throwable
	 */
	public function updateRepetitieTaken(CorveeRepetitie $repetitie, $verplaats) {
		return $this->_em->transactional(function () use ($repetitie, $verplaats) {
			$taken = $this->findBy(['verwijderd' => false, 'crv_repetitie_id' => $repetitie->crv_repetitie_id]);

			foreach ($taken as $taak) {
				$taak->functie_id = $repetitie->corveeFunctie->functie_id;
				$taak->punten = $repetitie->standaard_punten;

				$this->_em->persist($taak);
			}

			$this->_em->flush();
			$updatecount = count($taken);

			$taken = $this->findBy(['verwijderd' => false, 'crv_repetitie_id' => $repetitie->crv_repetitie_id]);
			$takenPerDatum = array(); // taken per datum indien geen maaltijd
			$takenPerMaaltijd = array(); // taken per maaltijd
			$maaltijden = ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->getKomendeRepetitieMaaltijden($repetitie->mlt_repetitie_id);
			/** @var Maaltijd[] $maaltijdenById */
			$maaltijdenById = array();
			foreach ($maaltijden as $maaltijd) {
				$takenPerMaaltijd[$maaltijd->maaltijd_id] = array();
				$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
			}
			// update day of the week
			$daycount = 0;
			foreach ($taken as $taak) {
				$datum = $taak->datum;
				if ($verplaats) {
					$shift = $repetitie->dag_vd_week - $datum->format('w');
					if ($shift > 0) {
						$datum = $datum->add(DateInterval::createFromDateString('+' . $shift . ' days'));
					} elseif ($shift < 0) {
						$datum = $datum->add(DateInterval::createFromDateString($shift . ' days'));
					}
					if ($shift !== 0) {
						$taak->datum = $datum;
						$this->_em->persist($taak);
						$daycount++;
					}
				}
				$mid = $taak->maaltijd_id;
				if ($mid !== null) {
					if (array_key_exists($mid, $maaltijdenById)) { // do not change if not komende repetitie maaltijd
						$takenPerMaaltijd[$mid][] = $taak;
					}
				} else {
					$takenPerDatum[date_format_intl($datum, DATE_FORMAT)][] = $taak;
				}
			}
			// standaard aantal aanvullen
			$datumcount = 0;
			foreach ($takenPerDatum as $datum => $taken) {
				$verschil = $repetitie->standaard_aantal - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					$taak = $this->vanRepetitie($repetitie, $taken[0]->datum, null, null, 0);
					$this->_em->persist($taak);
				}
				$datumcount += $verschil;
			}
			$maaltijdcount = 0;
			foreach ($takenPerMaaltijd as $mid => $taken) {
				$verschil = $repetitie->standaard_aantal - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					$taak = $this->vanRepetitie($repetitie, $maaltijdenById[$mid]->datum, $mid, null, 0);
					$this->_em->persist($taak);
				}
				$maaltijdcount += $verschil;
			}
			$this->_em->flush();
			return new RepetitieTakenUpdateDTO($updatecount, $daycount, $datumcount, $maaltijdcount);
		});
	}

}
