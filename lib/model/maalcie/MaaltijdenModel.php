<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use Exception;

/**
 * MaaltijdenModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class MaaltijdenModel extends PersistenceModel {

	const ORM = Maaltijd::class;

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
	 * @var ArchiefMaaltijdModel
	 */
	private $archiefMaaltijdModel;

	/**
	 * @var CorveeTakenModel
	 */
	private $corveeTakenModel;

	/**
	 * @var CorveeRepetitiesModel
	 */
	private $corveeRepetitiesModel;

	/**
	 * MaaltijdenModel constructor.
	 * @param MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	 * @param MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository
	 * @param ArchiefMaaltijdModel $archiefMaaltijdModel
	 * @param CorveeTakenModel $corveeTakenModel
	 * @param CorveeRepetitiesModel $corveeRepetitiesModel
	 */
	public function __construct(
        MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
        MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
        ArchiefMaaltijdModel $archiefMaaltijdModel,
        CorveeTakenModel $corveeTakenModel,
        CorveeRepetitiesModel $corveeRepetitiesModel
	) {
		parent::__construct();

		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->archiefMaaltijdModel = $archiefMaaltijdModel;
		$this->corveeTakenModel = $corveeTakenModel;
		$this->corveeRepetitiesModel = $corveeRepetitiesModel;
	}


	public function vanRepetitie(MaaltijdRepetitie $repetitie, $datum) {
		$maaltijd = new Maaltijd();
		$maaltijd->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
		$maaltijd->product_id = $repetitie->product_id;
		$maaltijd->titel = $repetitie->standaard_titel;
		$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
		$maaltijd->datum = date('Y-m-d', $datum);
		$maaltijd->tijd = $repetitie->standaard_tijd->format(TIME_FORMAT);
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
		$this->update($maaltijd);
		return $maaltijd;
	}

	public function sluitMaaltijd(Maaltijd $maaltijd) {
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is al gesloten');
		}
		$maaltijd->gesloten = true;
		$maaltijd->laatst_gesloten = date('Y-m-d H:i:s');
		$this->update($maaltijd);
	}

	public function getMaaltijden(
		$criteria = null,
		array $criteria_params = array(),
		$groupby = null,
		$orderby = null,
		$limit = null,
		$start = 0
	) {
		$filter = 'verwijderd = false';
		if ($criteria != null AND $criteria != '') {
			$filter .= ' AND ' . $criteria;
		}

		return $this->find($filter, $criteria_params, $groupby, $orderby, $limit, $start);
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
		$maaltijden = $this->find('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
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
		$maaltijden = $this->find('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d'), date('Y-m-d', strtotime(instelling('maaltijden', 'toon_ketzer_vooraf')))));
		$maaltijden = $this->filterMaaltijdenVoorLid($maaltijden, $uid, true);
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden in het verleden op voor de ingestelde periode.
	 *
	 * @return Maaltijd[]
	 */
	public function getRecenteMaaltijden($timestamp, $limit = null) {
		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->find('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $timestamp), date('Y-m-d')), null, null, $limit);
		$maaltijdenById = array();
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
		return $this->find('verwijderd = true');
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
		$maaltijd = $this->retrieveByPrimaryKey(array($mid));
		if ($maaltijd === false) throw new CsrGebruikerException('Maaltijd bestaat niet: ' . $mid);
		return $maaltijd;
	}

	/**
	 * @param Maaltijd $maaltijd
	 *
	 * @return array
	 */
	public function saveMaaltijd($maaltijd) {
		$verwijderd = 0;
		if ($maaltijd->maaltijd_id == 0) {
			$maaltijd->maaltijd_id = $this->create($maaltijd);
			$this->meldAboAan($maaltijd);
		} else {
			$this->update($maaltijd);
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
			$this->deleteByPrimaryKey(array($mid));
		} else {
			$maaltijd->verwijderd = true;
			$this->update($maaltijd);
		}
	}

	public function herstelMaaltijd($mid) {
		$maaltijd = $this->loadMaaltijd($mid);
		if (!$maaltijd->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is niet verwijderd');
		}
		$maaltijd->verwijderd = false;
		$this->update($maaltijd);
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
		$result = array();
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
		$errors = array();
		$maaltijden = $this->find('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)))->fetchAll();
		foreach ($maaltijden as $maaltijd) {
			try {
				$archief = $this->archiefMaaltijdModel->vanMaaltijd($maaltijd);
				$this->archiefMaaltijdModel->create($archief);
				if ($this->corveeTakenModel->existMaaltijdCorvee($maaltijd->maaltijd_id)) {
					setMelding($maaltijd->datum . ' ' . $maaltijd->titel . ' heeft nog gekoppelde corveetaken!', 2);
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
		return $this->find('mlt_repetitie_id = ? AND verwijderd = FALSE AND datum >= ?', array($mrid, date('Y-m-d')));
	}

	public function getKomendeOpenRepetitieMaaltijden($mrid) {
		return $this->find('mlt_repetitie_id = ? AND gesloten = FALSE AND verwijderd = FALSE AND datum >= ?', array($mrid, date('Y-m-d')));
	}

	public function verwijderRepetitieMaaltijden($mrid) {
		$maaltijden = $this->find('mlt_repetitie_id = ?', array($mrid));
		foreach ($maaltijden as $maaltijd) {
			$maaltijd->verwijderd = true;
			$this->update($maaltijd);
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
		return $this->count('mlt_repetitie_id = ?', array($mrid)) > 0;
	}

	public function updateRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $verplaats) {
		return Database::transaction(function () use ($repetitie, $verplaats) {
			// update day of the week & check filter
			$updated = 0;
			$aanmeldingen = 0;
			$maaltijden = $this->find('verwijderd = FALSE AND mlt_repetitie_id = ?', array($repetitie->mlt_repetitie_id));
			$filter = $repetitie->abonnement_filter;
			if (!empty($filter)) {
				$aanmeldingen = $this->maaltijdAanmeldingenRepository->checkAanmeldingenFilter($filter, $maaltijden);
			}
			foreach ($maaltijden as $maaltijd) {
				if ($verplaats) {
					$datum = strtotime($maaltijd->datum);
					$shift = $repetitie->dag_vd_week - date('w', $datum);
					if ($shift > 0) {
						$datum = strtotime('+' . $shift . ' days', $datum);
					} elseif ($shift < 0) {
						$datum = strtotime($shift . ' days', $datum);
					}
					$maaltijd->datum = date('Y-m-d', $datum);
				}
				$maaltijd->titel = $repetitie->standaard_titel;
				$maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
				$maaltijd->tijd = $repetitie->standaard_tijd->format(TIME_FORMAT);
				$maaltijd->product_id = $repetitie->product_id;
				$maaltijd->aanmeld_filter = $filter;
				try {
					$this->update($maaltijd);
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
	public function maakRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $beginDatum, $eindDatum) {
		$doctrine = ContainerFacade::getContainer()->get('doctrine');
		$conn = $doctrine->getConnection();
		$conn->beginTransaction();
		try {
			if ($repetitie->periode_in_dagen < 1) {
				throw new CsrGebruikerException('New repetitie-maaltijden faalt: $periode =' . $repetitie->periode_in_dagen);
			}

			// start at first occurence
			$shift = $repetitie->dag_vd_week - date('w', $beginDatum) + 7;
			$shift %= 7;
			if ($shift > 0) {
				$beginDatum = strtotime('+' . $shift . ' days', $beginDatum);
			}
			$datum = $beginDatum;
			$corveerepetities = $this->corveeRepetitiesModel->getRepetitiesVoorMaaltijdRepetitie($repetitie->mlt_repetitie_id)->fetchAll();
			$maaltijden = array();
			while ($datum <= $eindDatum) { // break after one

				$maaltijd = $this->vanRepetitie($repetitie, $datum);
				$maaltijd->maaltijd_id = $this->create($maaltijd);
				$this->meldAboAan($maaltijd);

				foreach ($corveerepetities as $corveerepetitie) {
					$this->corveeTakenModel->newRepetitieTaken($corveerepetitie, $datum, $datum, intval($maaltijd->maaltijd_id)); // do not repeat within maaltijd period
				}
				$maaltijden[] = $maaltijd;
				if ($repetitie->periode_in_dagen < 1) {
					break;
				}
				$datum = strtotime('+' . $repetitie->periode_in_dagen . ' days', $datum);
			}
			$conn->commit();
			return $maaltijden;
		} catch (Exception $ex) {
			$conn->rollBack();
			throw $ex;
		}

	}

}
