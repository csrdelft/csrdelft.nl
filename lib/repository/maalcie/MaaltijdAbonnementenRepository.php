<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;
use Throwable;

/**
 * MaaltijdAbonnementenRepository    |    P.W.G. Brussee (brussee@live.nl)
 *
 * @method MaaltijdAbonnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdAbonnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdAbonnement[]    findAll()
 * @method MaaltijdAbonnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdAbonnementenRepository extends AbstractRepository {
	const ORM = MaaltijdAbonnement::class;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(ManagerRegistry $registry, MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository) {
		parent::__construct($registry, MaaltijdAbonnement::class);
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	/**
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function getAbonnementenWaarschuwingenMatrix() {
		return $this->_em->transactional(function () {
			$abos = $this->findAll();

			$waarschuwingen = [];

			foreach ($abos as $abo) {
				$repetitie = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getRepetitie($abo->mlt_repetitie_id);
				if (!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter($abo->uid, $repetitie->abonnement_filter)) {
					$abo->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter;
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (!$repetitie->abonneerbaar) {
					$abo->foutmelding = 'Niet abonneerbaar';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (!LidStatus::isLidLike(ProfielRepository::get($abo->uid)->status)) {
					$abo->waarschuwing = 'Geen huidig lid';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			$repById = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getAlleRepetities(true);

			return $this->fillHoles($waarschuwingen, $repById);
		});
	}

	/**
	 * @param $matrix
	 * @param $repById
	 * @param bool $ingeschakeld
	 * @return array
	 */
	private function fillHoles($matrix, $repById, $ingeschakeld = false) {
		foreach ($repById as $mrid => $repetitie) { // vul gaten in matrix vanwege uitgeschakelde abonnementen
			foreach ($matrix as $uid => $abos) {
				if (!array_key_exists($mrid, $abos)) {
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $ingeschakeld ? $mrid : null;
					$abonnement->van_uid = $uid;
					$abonnement->maaltijd_repetitie = $repetitie;
					$matrix[$uid][$mrid] = $abonnement;
				}
				ksort($repById);
				ksort($matrix[$uid]);
			}
		}
		return array($matrix, $repById);
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getAbonnementenAbonneerbaarMatrix() {
		return $this->_em->transactional(function () {
			$repById = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getAlleRepetities(true); // grouped by mrid
			$sql = 'SELECT lid.uid AS van, r.mlt_repetitie_id AS mrid,';
			$sql .= ' r.abonnement_filter AS filter,'; // controleer later
			$sql .= ' (r.abonneerbaar = false) AS abo_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
			$sql .= ' (EXISTS ( SELECT * FROM mlt_abonnementen AS a WHERE a.mlt_repetitie_id = mrid AND a.uid = van )) AS abo';
			$sql .= ' FROM profielen AS lid, mlt_repetities AS r';
			$values = array();
			$sql .= ' WHERE lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")';
			$sql .= ' ORDER BY lid.achternaam, lid.voornaam ASC';
			$db = Database::instance()->getDatabase();
			$query = $db->prepare($sql);
			$query->execute($values);

			$leden = ContainerFacade::getContainer()->get(ProfielRepository::class)->ormFind("status = ? OR status = ? OR status = ?", LidStatus::getLidLike());

			$matrix = array();
			foreach ($leden as $lid) {
				$abos = $this->find("uid = ?", array($lid->uid));
				foreach ($abos as $abo) {
					$rep = $repById[$abo->mlt_repetitie_id];
					$abo->maaltijd_repetitie = $rep;
					if (!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter($lid->uid, $rep->abonnement_filter)) {
						$abo->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $rep->abonnement_filter;
					}
					$matrix[$lid->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			return $this->fillHoles($matrix, $repById);
		});
	}

	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 *
	 * @return MaaltijdAbonnement[][] 2d matrix met eerst uid, en dan repetitie id
	 * @throws Throwable
	 */
	public function getAbonnementenMatrix() {
		return $this->_em->transactional(function () {
			$repById = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getAlleRepetities(true); // grouped by mrid
			$sql = 'SELECT lid.uid AS van, r.mlt_repetitie_id AS mrid,';
			$sql .= ' r.abonnement_filter AS filter,'; // controleer later
			$sql .= ' (r.abonneerbaar = false) AS abo_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
			$sql .= ' (EXISTS ( SELECT * FROM mlt_abonnementen AS a WHERE a.mlt_repetitie_id = mrid AND a.uid = van )) AS abo';
			$sql .= ' FROM profielen AS lid, mlt_repetities AS r';
			$sql .= ' HAVING abo = true';
			$sql .= ' ORDER BY lid.achternaam, lid.voornaam ASC';
			$db = Database::instance()->getDatabase();
			$query = $db->prepare($sql);
			$query->execute();
			$abos = $query->fetchAll();

			$matrix = array();
			foreach ($abos as $abo) { // build matrix
				$mrid = $abo['mrid'];
				$uid = $abo['van'];
				if ($abo['abo']) { // ingeschakelde abonnementen
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $mrid;
					$abonnement->uid = $uid;
				} else { // uitgeschakelde abonnementen
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $mrid;
				}
				$abonnement->van_uid = $uid;
				$abonnement->maaltijd_repetitie = $repById[$mrid];
				// toon waarschuwingen
				if ($abo['abo_err']) {
					$abonnement->foutmelding = 'Niet abonneerbaar';
				} elseif ($abo['status_err']) {
					$abonnement->waarschuwing = 'Geen huidig lid';
				} elseif (!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter($uid, $abo['filter'])) {
					$abonnement->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $abo['filter'];
				}
				$matrix[$uid][$mrid] = $abonnement;
			}
			return $this->fillHoles($matrix, $repById, true);
		});
	}

	public function getAbonnementenVoorRepetitie($mrid) {
		return $this->findBy(['mlt_repetitie_id' => $mrid]);
	}

	/**
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function getAbonnementenVanNovieten() {
		return $this->_em->transactional(function () {
			$novieten = ContainerFacade::getContainer()->get(ProfielRepository::class)->ormFind('status = "S_NOVIET"');
			$matrix = array();
			foreach ($novieten as $noviet) {
				$matrix[$noviet->uid] = $this->findBy(['uid' => $noviet->uid], ['mlt_repetitie_id' => 'DESC']);
			}
			return $matrix;
		});
	}

	/**
	 * @param $abo MaaltijdAbonnement
	 * @return false|int
	 * @throws CsrGebruikerException
	 * @throws Throwable
	 */
	public function inschakelenAbonnement($abo) {
		return $this->_em->transactional(function () use ($abo) {
			$repetitie = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getRepetitie($abo->mlt_repetitie_id);
			if (!$repetitie->abonneerbaar) {
				throw new CsrGebruikerException('Niet abonneerbaar');
			}
			if ($this->find(['mlt_repetitie_id' => $abo->mlt_repetitie_id, 'uid' => $abo->uid])) {
				throw new CsrGebruikerException('Abonnement al ingeschakeld');
			}
			if (!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter($abo->uid, $repetitie->abonnement_filter)) {
				throw new CsrGebruikerException('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
			}

			$abo->van_uid = $abo->uid;
			$abo->wanneer_ingeschakeld = date_create_immutable();
			$this->_em->persist($abo);
			$this->_em->flush();

			return $this->maaltijdAanmeldingenRepository->aanmeldenVoorKomendeRepetitieMaaltijden($abo->mlt_repetitie_id, $abo->uid);
		});
	}

	/**
	 * @param $mrid
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function inschakelenAbonnementVoorNovieten($mrid) {
		return $this->_em->transactional(function () use ($mrid) {
			$novieten = ContainerFacade::getContainer()->get(ProfielRepository::class)->ormFind('status = "S_NOVIET"');

			$aantal = 0;
			foreach ($novieten as $noviet) {
				$repetitie = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class)->getRepetitie($mrid);
				if (!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter($noviet->uid, $repetitie->abonnement_filter)) {
					continue;
				}

				$abo = new MaaltijdAbonnement();
				$abo->mlt_repetitie_id = $mrid;
				$abo->uid = $noviet->uid;
				$abo->wanneer_ingeschakeld = date('Y-m-d H:i');

				if ($this->find(['mlt_repetitie_id' => $abo->mlt_repetitie_id, 'uid' => $abo->uid])) {
					continue;
				}
				$this->_em->persist($abo);
				$this->maaltijdAanmeldingenRepository->aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $noviet->uid);
				$aantal += 1;
			}

			$this->_em->flush();

			return $aantal;
		});
	}

	/**
	 * @param $mrid
	 * @param $uid
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function uitschakelenAbonnement($mrid, $uid) {
		return $this->_em->transactional(function () use ($mrid, $uid) {
			if (!$this->getHeeftAbonnement($mrid, $uid)) {
				throw new CsrGebruikerException('Abonnement al uitgeschakeld');
			}

			$abo = $this->find(['mlt_repetitie_id' => $mrid, 'uid' => $uid]);
			$this->_em->remove($abo);
			$this->_em->flush();
			$abo = new MaaltijdAbonnement();
			$abo->mlt_repetitie_id = $mrid;
			$abo->van_uid = $uid;

			$aantal = $this->maaltijdAanmeldingenRepository->afmeldenDoorAbonnement($mrid, $uid);
			return array($abo, $aantal);
		});
	}

	public function getHeeftAbonnement($mrid, $uid) {
		return $this->find(['mlt_repetitie_id' => $mrid, 'uid' => $uid]) != null;
	}

	/**
	 * Called when a MaaltijdRepetitie is being deleted.
	 * This is only possible after all MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement,
	 * by deleting the Maaltijden (db foreign key door_abonnement)
	 *
	 * @param $mrid
	 * @return int amount of deleted abos
	 * @throws Throwable
	 */
	public function verwijderAbonnementen($mrid) {
		return $this->_em->transactional(function () use ($mrid) {
			/** @var MaaltijdAbonnement[] $abos */
			$abos = $this->findBy(['mlt_repetitie_id' => $mrid]);
			$aantal = count($abos);
			foreach ($abos as $abo) {
				$this->maaltijdAanmeldingenRepository->afmeldenDoorAbonnement($mrid, $abo->uid);
				$this->_em->remove($abo);
			}
			$this->_em->flush();
			return $aantal;
		});
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * All linked MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement.
	 *
	 * @param $uid
	 * @return int amount of deleted abos
	 * @throws Throwable
	 */
	public function verwijderAbonnementenVoorLid($uid) {
		return $this->_em->transactional(function () use ($uid) {
			$abos = $this->getAbonnementenVoorLid($uid);
			$aantal = 0;
			foreach ($abos as $abo) {
				$aantal++;
				$this->_em->remove($abo);
			}
			$this->_em->flush();

			if (sizeof($abos) !== $aantal) {
				setMelding('Niet alle abonnementen zijn uitgeschakeld!', -1);
			}
			return $aantal;
		});
	}

	/**
	 * Geeft de ingeschakelde abonnementen voor een lid terug plus
	 * de abonnementen die nog kunnen worden ingeschakeld op basis
	 * van de meegegeven maaltijdrepetities.
	 *
	 * @param string $uid
	 * @param boolean $abonneerbaar alleen abonneerbare abonnementen
	 * @param boolean $uitgeschakeld ook uitgeschakelde abonnementen
	 * @return MaaltijdAbonnement[]
	 * @throws Throwable
	 */
	public function getAbonnementenVoorLid($uid, $abonneerbaar = false, $uitgeschakeld = false) {
		$lijst = [];
		$this->_em->transactional(function () use ($lijst, $uid, $abonneerbaar, $uitgeschakeld) {
			$maaltijdRepetitiesRepository = ContainerFacade::getContainer()->get(MaaltijdRepetitiesRepository::class);
			if ($abonneerbaar) {
				$repById = $maaltijdRepetitiesRepository->getAbonneerbareRepetitiesVoorLid($uid); // grouped by mrid
			} else {
				$repById = $maaltijdRepetitiesRepository->getAlleRepetities(true); // grouped by mrid
			}
			$abos = $this->findBy(['uid' => $uid]);
			foreach ($abos as $abo) { // ingeschakelde abonnementen
				$mrid = $abo->mlt_repetitie_id;
				if (!array_key_exists($mrid, $repById)) { // ingeschakelde abonnementen altijd weergeven
					$repById[$mrid] = $maaltijdRepetitiesRepository->getRepetitie($mrid);
				}
				$abo->maaltijd_repetitie = $repById[$mrid];
				$abo->van_uid = $uid;
				$lijst[$mrid] = $abo;
			}
			if ($uitgeschakeld) {
				foreach ($repById as $repetitie) {
					$mrid = $repetitie->mlt_repetitie_id;
					if (!array_key_exists($mrid, $lijst)) { // uitgeschakelde abonnementen weergeven
						$abo = new MaaltijdAbonnement();
						$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
						$abo->maaltijd_repetitie = $repetitie;
						$abo->van_uid = $uid;
						$lijst[$mrid] = $abo;
					}
				}
			}
			ksort($lijst);
		});

		return $lijst;
	}
}
