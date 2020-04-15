<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\CorveeRepetitiesModel;
use CsrDelft\model\maalcie\CorveeTakenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractRepository;
use DateInterval;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * MaaltijdenRepository  |  P.W.G. Brussee (brussee@live.nl)
 *
 * @method Maaltijd|null find($id, $lockMode = null, $lockVersion = null)
 * @method Maaltijd|null findOneBy(array $criteria, array $orderBy = null)
 * @method Maaltijd[]    findAll()
 * @method Maaltijd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdenRepository extends AbstractRepository {
	protected $default_order = 'datum ASC, tijd ASC';

	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	/**
	 * @var MaaltijdAbonnementenRepository
	 */
	private $maaltijdAbonnementenRepository;

	/**
	 * @var ArchiefMaaltijdenRepository
	 */
	private $archiefMaaltijdenRepository;

	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;

	/**
	 * @var CorveeRepetitiesModel
	 */
	private $corveeRepetitiesModel;

	/**
	 * @param ManagerRegistry $registry
	 * @param MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	 * @param MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository
	 * @param ArchiefMaaltijdenRepository $archiefMaaltijdenRepository
	 * @param CorveeTakenModel $corveeTakenModel
	 * @param CorveeRepetitiesModel $corveeRepetitiesModel
	 */
	public function __construct(
		ManagerRegistry $registry,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		ArchiefMaaltijdenRepository $archiefMaaltijdenRepository,
		CorveeTakenModel $corveeTakenModel,
		CorveeRepetitiesModel $corveeRepetitiesModel
	) {
		parent::__construct($registry, Maaltijd::class);

		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->archiefMaaltijdenRepository = $archiefMaaltijdenRepository;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->corveeRepetitiesModel = $corveeRepetitiesModel;
	}


	public function vanRepetitie(MaaltijdRepetitie $repetitie, DateTimeInterface $datum) {
		$maaltijd = new Maaltijd();
		$maaltijd->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$maaltijd->product_id = $repetitie->product_id;
		$maaltijd->titel = $repetitie->standaard_titel;
		$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
		$maaltijd->datum = $datum;
		$maaltijd->tijd = $repetitie->standaard_tijd;
		$maaltijd->aanmeld_filter = $repetitie->abonnement_filter;
		$maaltijd->omschrijving = null;
		$maaltijd->verwerkt = false;

		return $maaltijd;
	}

	public function openMaaltijd(Maaltijd $maaltijd) {
		if (!$maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is al geopend');
		}
		$maaltijd->gesloten = false;
		$this->_em->persist($maaltijd);
		$this->_em->flush();
		return $maaltijd;
	}

	public function sluitMaaltijd(Maaltijd $maaltijd) {
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is al gesloten');
		}
		$maaltijd->gesloten = true;
		$maaltijd->laatst_gesloten = date_create_immutable();
		$this->_em->persist($maaltijd);
		$this->_em->flush();
	}

	/**
	 * @return Maaltijd[]
	 */
	public function getMaaltijdenToekomst() {
		return $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum > :datum')
			->setParameter(':datum', date_create('-1 week'))
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
	}

	public function getMaaltijdenHistorie() {
		return $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum <= CURDATE()')
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
	}

	public function getMaaltijden() {
		return $this->findBy(['verwijderd' => false]);
	}

	/**
	 * Haalt de maaltijden op voor het ingelode lid tussen de opgegeven data.
	 *
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 *
	 * @return Maaltijd[] implements Agendeerbaar
	 * @throws CsrException
	 */
	public function getMaaltijdenVoorAgenda($van, $tot) {
		if (!is_int($van)) {
			throw new CsrException('Invalid timestamp: $van getMaaltijdenVoorAgenda()');
		}
		if (!is_int($tot)) {
			throw new CsrException('Invalid timestamp: $tot getMaaltijdenVoorAgenda()');
		}
		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum >= :van_datum and m.datum <= :tot_datum')
			->setParameter('van_datum', date_create("@$van"))
			->setParameter('tot_datum', date_create("@$tot"))
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
		$maaltijden = $this->filterMaaltijdenVoorLid($maaltijden, LoginModel::getUid());
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden op die beschikbaar zijn voor aanmelding voor het lid in de ingestelde periode vooraf.
	 *
	 * @param string $uid
	 *
	 * @return Maaltijd[]
	 */
	public function getKomendeMaaltijdenVoorLid($uid) {
		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum >= :van_datum and m.datum <= :tot_datum')
			->setParameter('van_datum', date_create())
			->setParameter('tot_datum', date_create(instelling('maaltijden', 'toon_ketzer_vooraf')))
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
		$maaltijden = $this->filterMaaltijdenVoorLid($maaltijden, $uid, true);
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden in het verleden op voor de ingestelde periode.
	 *
	 * @param DateTimeInterface $timestamp
	 * @param null $limit
	 * @return Maaltijd[]
	 */
	public function getRecenteMaaltijden(DateTimeInterface $timestamp, $limit = null) {
		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum >= :van_datum and m.datum <= :tot_datum')
			->setParameter('van_datum', $timestamp)
			->setParameter('tot_datum', date_create())
			->setMaxResults($limit)
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
		$maaltijdenById = [];
		foreach ($maaltijden as $maaltijd) {
			// Sla over als maaltijd nog niet voorbij is
			if ($maaltijd->getEindMoment() > time()) continue;
			$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
		}
		return $maaltijdenById;
	}

	/**
	 * Haalt de maaltijd op die in een ketzer zal worden weergegeven.
	 *
	 * @return Maaltijd|false
	 */
	public function getMaaltijdVoorKetzer($mid) {
		$maaltijden = array($this->getMaaltijd($mid));
		$maaltijden = $this->filterMaaltijdenVoorLid($maaltijden, LoginModel::getUid());
		if (!empty($maaltijden)) {
			return reset($maaltijden);
		}
		return false;
	}

	public function getVerwijderdeMaaltijden() {
		return $this->findBy(['verwijderd' => 'true']);
	}

	/**
	 * @param $mid
	 * @param bool $verwijderd
	 *
	 * @return Maaltijd
	 * @throws CsrGebruikerException
	 */
	public function getMaaltijd($mid, $verwijderd = false) {
		$maaltijd = $this->loadMaaltijd($mid);
		if (!$verwijderd && $maaltijd->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}
		return $maaltijd;
	}

	private function loadMaaltijd($mid) {
		$maaltijd = $this->find($mid);
		if (!$maaltijd) throw new CsrGebruikerException('Maaltijd bestaat niet: ' . $mid);
		return $maaltijd;
	}

	/**
	 * @param Maaltijd $maaltijd
	 *
	 * @return array
	 */
	public function saveMaaltijd($maaltijd) {
		$verwijderd = 0;
		if (!$maaltijd->maaltijd_id) {
			$this->_em->persist($maaltijd);
			$this->_em->flush();
			$this->meldAboAan($maaltijd);
		} else {
			$this->_em->persist($maaltijd);
			$this->_em->flush();
			if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
				$this->sluitMaaltijd($maaltijd);
			}
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd && !empty($filter)) {
				$verwijderd = $this->maaltijdAanmeldingenRepository->checkAanmeldingenFilter($filter, array($maaltijd));
				$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - $verwijderd;
			}
		}
		return array($maaltijd, $verwijderd);
	}

	public function prullenbakLeegmaken() {
		$aantal = 0;
		$maaltijden = $this->getVerwijderdeMaaltijden();
		foreach ($maaltijden as $maaltijd) {
			try {
				$this->verwijderMaaltijd($maaltijd->maaltijd_id);
				$aantal++;
			} catch (CsrGebruikerException $e) {
				setMelding($e->getMessage(), -1);
			}
		}
		return $aantal;
	}

	public function verwijderMaaltijd($mid) {
		$maaltijd = $this->loadMaaltijd($mid);
		$this->corveeTakenModel->verwijderMaaltijdCorvee($mid); // delete corveetaken first (foreign key)
		if ($maaltijd->verwijderd) {
			if ($this->corveeTakenModel->existMaaltijdCorvee($mid)) {
				throw new CsrGebruikerException('Er zitten nog bijbehorende corveetaken in de prullenbak. Verwijder die eerst definitief!');
			}
			$this->maaltijdAanmeldingenRepository->deleteAanmeldingenVoorMaaltijd($mid);
			$this->_em->remove($maaltijd);
			$this->_em->flush();
		} else {
			$maaltijd->verwijderd = true;
			$this->_em->persist($maaltijd);
			$this->_em->flush();
		}
	}

	public function herstelMaaltijd($mid) {
		$maaltijd = $this->loadMaaltijd($mid);
		if (!$maaltijd->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is niet verwijderd');
		}
		$maaltijd->verwijderd = false;
		$this->_em->persist($maaltijd);
		$this->_em->flush();
		return $maaltijd;
	}

	/**
	 * Filtert de maaltijden met het aanmeld-filter van de maaltijd op de permissies van het lid.
	 *
	 * @param Maaltijd[] $maaltijden
	 * @param string $uid
	 * @param bool $verbergVerleden
	 *
	 * @return Maaltijd[]
	 */
	private function filterMaaltijdenVoorLid($maaltijden, $uid, $verbergVerleden = false) {
		$result = [];
		foreach ($maaltijden as $maaltijd) {
			// Verberg afgelopen maaltijd
			if ($verbergVerleden && $maaltijd->getEindMoment() < time()) continue;

			// Kan en mag aanmelden of mag maaltijdlijst zien en sluiten? Dan maaltijd ook zien.
			if (($maaltijd->aanmeld_limiet > 0 AND $this->maaltijdAanmeldingenRepository->checkAanmeldFilter($uid, $maaltijd->aanmeld_filter)) OR $maaltijd->magBekijken($uid)) {
				$result[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		return $result;
	}

	/**
	 * @param Maaltijd $maaltijd
	 */
	public function meldAboAan($maaltijd) {
		$aantal = 0;
		// aanmelden van leden met abonnement op deze repetitie
		if (!$maaltijd->gesloten && $maaltijd->mlt_repetitie_id !== null) {
			$abonnementen = $this->maaltijdAbonnementenRepository->getAbonnementenVoorRepetitie($maaltijd->mlt_repetitie_id);
			foreach ($abonnementen as $abo) {
				if ($this->maaltijdAanmeldingenRepository->checkAanmeldFilter($abo->uid, $maaltijd->aanmeld_filter)) {
					if ($this->maaltijdAanmeldingenRepository->aanmeldenDoorAbonnement($maaltijd, $abo->mlt_repetitie_id, $abo->uid)) {
						$aantal++;
					}
				}
			}
		}
		$maaltijd->aantal_aanmeldingen = $aantal;
	}

	// Archief-Maaltijden ############################################################

	public function archiveerOudeMaaltijden($van, $tot) {
		if (!is_int($van) || !is_int($tot)) {
			throw new CsrException('Invalid timestamp: archiveerOudeMaaltijden()');
		}
		$errors = [];
		$maaltijden = $this->createQueryBuilder('m')
			->where('m.verwijderd = false and m.datum >= :van_datum and datum <= :tot_datum')
			->setParameter('van_datum', $van)
			->setParameter('tot_datum', $tot)
			->orderBy('m.datum', 'ASC')
			->orderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
		foreach ($maaltijden as $maaltijd) {
			try {
				$archief = $this->archiefMaaltijdenRepository->vanMaaltijd($maaltijd);
				$this->archiefMaaltijdenRepository->create($archief);
				if ($this->corveeTakenModel->existMaaltijdCorvee($maaltijd->maaltijd_id)) {
					setMelding($maaltijd->getMoment()->format(DATETIME_FORMAT) . ' heeft nog gekoppelde corveetaken!', 2);
				}
			} catch (CsrGebruikerException $e) {
				$errors[] = $e;
				setMelding($e->getMessage(), -1);
			}
		}
		return array($errors, count($maaltijden));
	}

	// Repetitie-Maaltijden ############################################################

	public function getKomendeRepetitieMaaltijden($mrid) {
		return $this->createQueryBuilder('m')
			->where('m.mlt_repetitie_id = :maaltijd_id and verwijderd = false and datum >= :datum')
			->setParameter('maaltijd_id', $mrid)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
	}

	public function getKomendeOpenRepetitieMaaltijden($mrid) {
		return $this->createQueryBuilder('m')
			->where('m.mlt_repetitie_id = :repetitie and gesloten = false and verwijderd = false and datum >= :datum')
			->setParameter('repetitie', $mrid)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();
	}

	public function verwijderRepetitieMaaltijden($mrid) {
		$maaltijden = $this->findBy(['mlt_repetitie_id' => $mrid]);
		foreach ($maaltijden as $maaltijd) {
			$maaltijd->verwijderd = true;
			$this->_em->persist($maaltijd);
			$this->_em->flush();
		}
	}

	/**
	 * Called when a MaaltijdRepetitie is updated or is going to be deleted.
	 *
	 * @param int $mrid
	 *
	 * @return bool
	 */
	public function existRepetitieMaaltijden($mrid) {
		return $this->count(['mlt_repetitie_id' => $mrid]) > 0;
	}

	public function updateRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $verplaats) {
		return $this->_em->transactional(function () use ($repetitie, $verplaats) {
			// update day of the week & check filter
			$updated = 0;
			$aanmeldingen = 0;
			$maaltijden = $this->findBy(['verwijderd' => false, 'mlt_repetitie_id' => $repetitie->mlt_repetitie_id]);
			$filter = $repetitie->abonnement_filter;
			if (!empty($filter)) {
				$aanmeldingen = $this->maaltijdAanmeldingenRepository->checkAanmeldingenFilter($filter, $maaltijden);
			}
			foreach ($maaltijden as $maaltijd) {
				if ($verplaats) {
					$shift = $repetitie->dag_vd_week - $maaltijd->datum->format('w');
					if ($shift > 0) {
						$maaltijd->datum = $maaltijd->datum->add(DateInterval::createFromDateString('+' . $shift . 'days'));
					} elseif ($shift < 0) {
						$maaltijd->datum = $maaltijd->datum->add(DateInterval::createFromDateString($shift . ' days'));
					}
				}
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd;
				$maaltijd->product_id = $repetitie->product_id;
				$maaltijd->aanmeld_filter = $filter;
				try {
					$this->_em->persist($maaltijden);
					$this->_em->flush();
					$updated++;
				} catch (Exception $e) {

				}
			}
			return array($updated, $aanmeldingen);
		});
	}

	/**
	 * Maakt nieuwe maaltijden aan volgens de definitie van de maaltijd-repetitie.
	 * Alle leden met een abonnement hierop worden automatisch aangemeld.
	 *
	 * @param MaaltijdRepetitie $repetitie
	 * @param int $beginDatum
	 * @param int $eindDatum
	 *
	 * @return Maaltijd[]
	 * @throws CsrGebruikerException
	 */
	public function maakRepetitieMaaltijden(MaaltijdRepetitie $repetitie, DateTimeInterface $beginDatum, DateTimeInterface $eindDatum) {
		return $this->_em->transactional(function () use ($repetitie, $beginDatum, $eindDatum) {
			if ($repetitie->periode_in_dagen < 1) {
				throw new CsrGebruikerException('New repetitie-maaltijden faalt: $periode =' . $repetitie->periode_in_dagen);
			}

			// start at first occurence
			$shift = $repetitie->dag_vd_week - $beginDatum->format('w') + 7;
			$shift %= 7;
			if ($shift > 0) {
				$beginDatum = $beginDatum->add(DateInterval::createFromDateString("+{$shift} days"));
			}
			$datum = $beginDatum;
			$corveerepetities = $this->corveeRepetitiesModel->getRepetitiesVoorMaaltijdRepetitie($repetitie->mlt_repetitie_id)->fetchAll();
			$maaltijden = [];
			while ($datum <= $eindDatum) { // break after one

				$maaltijd = $this->vanRepetitie($repetitie, $datum);
				$this->_em->persist($maaltijd);
				$this->_em->flush();
				$this->meldAboAan($maaltijd);

				foreach ($corveerepetities as $corveerepetitie) {
					$this->corveeTakenModel->newRepetitieTaken($corveerepetitie, $datum->format(DATE_FORMAT), $datum->format(DATE_FORMAT), intval($maaltijd->maaltijd_id)); // do not repeat within maaltijd period
				}
				$maaltijden[] = $maaltijd;
				if ($repetitie->periode_in_dagen < 1) {
					break;
				}
				$datum = $datum->add(DateInterval::createFromDateString('+' . $repetitie->periode_in_dagen . ' days'));
			}
			return $maaltijden;
		});

	}

	public function update(Maaltijd $maaltijd) {
		$this->_em->persist($maaltijd);
		$this->_em->flush();
	}

	public function delete(Maaltijd $maaltijd) {
		$this->_em->remove($maaltijd);
		$this->_em->flush();
	}

}
