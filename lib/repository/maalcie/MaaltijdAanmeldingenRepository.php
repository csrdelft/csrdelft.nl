<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\model\entity\fiscaat\CiviBestelling;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\fiscaat\CiviProductModel;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * @method MaaltijdAanmelding|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaaltijdAanmelding|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaaltijdAanmelding[]    findAll()
 * @method MaaltijdAanmelding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaaltijdAanmeldingenRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, MaaltijdAanmelding::class);
	}

	public function aanmeldenVoorMaaltijd(
		Maaltijd $maaltijd,
		$uid,
		$doorUid,
		$aantalGasten = 0,
		$beheer = false,
		$gastenEetwens = ''
	) {
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			$this->assertMagAanmelden($maaltijd, $uid);
		}

		if ($this->getIsAangemeld($maaltijd->maaltijd_id, $uid)) {
			if (!$beheer) {
				throw new CsrGebruikerException('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasten door beheerder
			$aanmelding = $this->loadAanmelding($maaltijd->maaltijd_id, $uid);
			$verschil = $aantalGasten - $aanmelding->aantal_gasten;
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->laatst_gewijzigd = date_create_immutable();
			$this->getEntityManager()->persist($aanmelding);
			$this->getEntityManager()->flush();
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
		} else {
			$aanmelding = new MaaltijdAanmelding();
			$aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
			$aanmelding->uid = $uid;
			$aanmelding->door_uid = $doorUid;
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->gasten_eetwens = $gastenEetwens;
			$aanmelding->laatst_gewijzigd = date_create_immutable();
			$this->getEntityManager()->persist($aanmelding);
			$this->getEntityManager()->flush();

			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + 1 + $aantalGasten;
		}
		$aanmelding->maaltijd = $maaltijd;
		return $aanmelding;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param string $uid
	 * @throws CsrGebruikerException
	 */
	protected function assertMagAanmelden(Maaltijd $maaltijd, $uid) {
		if (CiviSaldoModel::instance()->getSaldo($uid) === false) {
			throw new CsrGebruikerException('Aanmelden voor maaltijden niet toegestaan, geen CiviSaldo.');
		}
		if (!$this->checkAanmeldFilter($uid, $maaltijd->aanmeld_filter)) {
			throw new CsrGebruikerException('Niet toegestaan vanwege aanmeldrestrictie: ' . $maaltijd->aanmeld_filter);
		}
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet) {
			throw new CsrGebruikerException('Maaltijd zit al vol');
		}
	}

	/**
	 * @param string $uid
	 * @param string $filter
	 * @return bool Of de gebruiker voldoet aan het filter
	 * @throws CsrGebruikerException Als de gebruiker niet bestaat
	 */
	public function checkAanmeldFilter($uid, $filter) {
		$account = AccountModel::get($uid); // false if account does not exist
		if (!$account) {
			throw new CsrGebruikerException('Lid bestaat niet: $uid =' . $uid);
		}
		if (empty($filter)) {
			return true;
		}
		return AccessModel::mag($account, $filter);
	}

	public function getIsAangemeld($mid, $uid) {
		return $this->find(['maaltijd_id' => $mid, 'uid' => $uid]) != null;
	}

	public function loadAanmelding($mid, $uid) {
		$aanmelding = $this->find(['maaltijd_id' => $mid, 'uid' => $uid]);
		if ($aanmelding == null) {
			throw new CsrGebruikerException('Load aanmelding faalt: Not found $mid =' . $mid);
		}
		return $aanmelding;
	}

	/**
	 * Called when a MaaltijdAbonnement is being deleted (turned off) or a MaaltijdRepetitie is being deleted.
	 *
	 * @param int $mrid id van de betreffede MaaltijdRepetitie
	 * @param string $uid Lid voor wie het MaaltijdAbonnement wordt uitschakeld
	 *
	 * @return int|null
	 */
	public function afmeldenDoorAbonnement($mrid, $uid) {
		// afmelden bij maaltijden waarbij dit abonnement de aanmelding heeft gedaan
		$maaltijden = MaaltijdenModel::instance()->getKomendeOpenRepetitieMaaltijden($mrid);
		if (empty($maaltijden)) {
			return 0;
		}
		$byMid = array();
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$byMid[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		$aanmeldingen = $this->getAanmeldingenVoorLid($byMid, $uid);
		$aantal = 0;
		foreach ($aanmeldingen as $mid => $aanmelding) {
			if ($mrid === $aanmelding->door_abonnement) {
				$this->getEntityManager()->remove($aanmelding);
				$aantal++;
			}
		}
		$this->getEntityManager()->flush();
		return $aantal;
	}

	public function getAanmeldingenVoorLid($maaltijdenById, $uid) {
		if (empty($maaltijdenById)) {
			return $maaltijdenById; // array()
		}

		$aanmeldingen = array();
		foreach ($maaltijdenById as $maaltijd) {
			$aanmeldingen[] = $this->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => $uid]);
		}

		$result = array();
		foreach ($aanmeldingen as $aanmelding) {
			if ($aanmelding) {

				$aanmelding->maaltijd = $maaltijdenById[$aanmelding->maaltijd_id];
				$result[$aanmelding->maaltijd_id] = $aanmelding;
			}
		}
		return $result;
	}

	public function afmeldenDoorLid(Maaltijd $maaltijd, $uid, $beheer = false) {
		if (!$this->getIsAangemeld($maaltijd->maaltijd_id, $uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$aanmelding = $this->loadAanmelding($maaltijd->maaltijd_id, $uid);
		$this->getEntityManager()->remove($aanmelding);
		$this->getEntityManager()->flush();
		$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->aantal_gasten;
		return $maaltijd;
	}

	public function saveGasten($mid, $uid, $gasten) {
		if (!is_numeric($mid) || $mid <= 0) {
			throw new CsrGebruikerException('Save gasten faalt: Invalid $mid =' . $mid);
		}
		if (!is_numeric($gasten) || $gasten < 0) {
			throw new CsrGebruikerException('Save gasten faalt: Invalid $gasten =' . $gasten);
		}
		if (!$this->getIsAangemeld($mid, $uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}

		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$aanmelding = $this->loadAanmelding($mid, $uid);
		$verschil = $gasten - $aanmelding->aantal_gasten;
		if ($maaltijd->getAantalAanmeldingen() + $verschil > $maaltijd->aanmeld_limiet) {
			throw new CsrGebruikerException('Maaltijd zit te vol');
		}
		if ($aanmelding->aantal_gasten !== $gasten) {
			$aanmelding->laatst_gewijzigd = date_create_immutable();
		}
		$aanmelding->aantal_gasten = $gasten;
		$this->getEntityManager()->persist($aanmelding);
		$this->getEntityManager()->flush();
		$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
		$aanmelding->maaltijd = $maaltijd;
		return $aanmelding;
	}

	public function saveGastenEetwens($mid, $uid, $opmerking) {
		if (!is_numeric($mid) || $mid <= 0) {
			throw new CsrGebruikerException('Save gasten eetwens faalt: Invalid $mid =' . $mid);
		}
		if (!$this->getIsAangemeld($mid, $uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}

		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$aanmelding = $this->loadAanmelding($mid, $uid);
		if ($aanmelding->aantal_gasten <= 0) {
			throw new CsrGebruikerException('Geen gasten aangemeld');
		}
		$aanmelding->maaltijd = $maaltijd;
		$aanmelding->gasten_eetwens = $opmerking;
		$this->getEntityManager()->persist($aanmelding);
		$this->getEntityManager()->flush();
		return $aanmelding;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return MaaltijdAanmelding[]
	 */
	public function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd) {
		$aanmeldingen = $this->findBy(['maaltijd_id' => $maaltijd->maaltijd_id]);
		$lijst = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->maaltijd = $maaltijd;
			$naam = ProfielRepository::getNaam($aanmelding->uid, 'streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->aantal_gasten; $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->door_uid = ($aanmelding->uid);
				$lijst[$naam . 'gast' . $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public function getRecenteAanmeldingenVoorLid($uid, $timestamp) {
		$maaltijdenById = MaaltijdenModel::instance()->getRecenteMaaltijden($timestamp);
		return $this->getAanmeldingenVoorLid($maaltijdenById, $uid);
	}

	/**
	 * Called when a Maaltijd is being deleted.
	 *
	 * @param int $mid maaltijd-id
	 */
	public function deleteAanmeldingenVoorMaaltijd($mid) {
		$aanmeldingen = $this->findBy(['maaltijd_id', $mid]);
		foreach ($aanmeldingen as $aanmelding) {
			$this->getEntityManager()->remove($aanmelding);
		}
		$this->getEntityManager()->flush();
	}

	/**
	 * Controleer of alle aanmeldingen voor de maaltijden nog in overeenstemming zijn met het aanmeldfilter.
	 *
	 * @param string $filter
	 * @param Maaltijd[] $maaltijden
	 * @return int
	 */
	public function checkAanmeldingenFilter($filter, $maaltijden) {
		$mids = array();
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$mids[] = $maaltijd->maaltijd_id;
			}
		}
		if (empty($mids)) {
			return 0;
		}
		$aantal = 0;
		$aanmeldingen = array();
		foreach ($mids as $mid) {
			$aanmeldingen = array_merge($aanmeldingen, $this->findBy(['maaltijd_id' => $mid]));
		}
		foreach ($aanmeldingen as $aanmelding) { // check filter voor elk aangemeld lid
			$uid = $aanmelding->uid;
			if (!$this->checkAanmeldFilter($uid, $filter)) { // verwijder aanmelding indien niet toegestaan
				$aantal += 1 + $aanmelding->aantal_gasten;
				$this->getEntityManager()->remove($aanmelding);
			}
		}
		$this->getEntityManager()->flush();
		return $aantal;
	}

	public function maakCiviBestelling(MaaltijdAanmelding $aanmelding) {
		$bestelling = new CiviBestelling();
		$bestelling->cie = 'maalcie';
		$bestelling->uid = $aanmelding->uid;
		$bestelling->deleted = false;
		$bestelling->moment = getDateTime();
		$bestelling->comment = sprintf('Datum maaltijd: %s', date('Y-M-d', $aanmelding->getMaaltijd()->getBeginMoment()));

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->product_id = $aanmelding->getMaaltijd()->product_id;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = CiviProductModel::instance()->getProduct($inhoud->product_id)->prijs * (1 + $aanmelding->aantal_gasten);

		return $bestelling;
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * Alleen aanroepen voor inschakelen abonnement!
	 *
	 * @param int $mrid
	 * @param string $uid
	 * @return int|false aantal aanmeldingen or false
	 * @throws CsrGebruikerException indien niet toegestaan vanwege aanmeldrestrictie
	 */
	public function aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid) {
		$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
		if (!$this->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
			throw new CsrGebruikerException('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
		}

		$aantal = 0;

		$maaltijden = MaaltijdenModel::instance()->find("mlt_repetitie_id = ? AND gesloten = false AND verwijderd = false AND datum >= ?", array($mrid, date('Y-m-d')));
		foreach ($maaltijden as $maaltijd) {
			if (!$this->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => $uid])) {
				if ($this->aanmeldenDoorAbonnement($maaltijd, $mrid, $uid)) {
					$aantal++;
				}
			}
		}
		return $aantal;
	}

	public function aanmeldenDoorAbonnement(Maaltijd $maaltijd, $mrid, $uid) {
		if (!$this->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => $uid])) {
			try {
				$this->assertMagAanmelden($maaltijd, $uid);

				$aanmelding = new MaaltijdAanmelding();
				$aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
				$aanmelding->uid = $uid;
				$aanmelding->door_uid = $uid;
				$aanmelding->door_abonnement = $mrid;
				$aanmelding->laatst_gewijzigd = date_create_immutable();
				$aanmelding->gasten_eetwens = '';

				$this->getEntityManager()->persist($aanmelding);
				$this->getEntityManager()->flush();

				return true;
			} catch (CsrGebruikerException $e) {
				return false;
			}
		}

		return false;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @return integer
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getAantalAanmeldingen(Maaltijd $maaltijd) {
		return $this->createQueryBuilder('maaltijd_aanmelding')
			->select('SUM(maaltijd_aanmelding.aantal_gasten) + COUNT(maaltijd_aanmelding.uid)')
			->where('maaltijd_aanmelding.maaltijd_id = :maaltijd_id')
			->setParameter('maaltijd_id', $maaltijd->maaltijd_id)
			->getQuery()->getSingleScalarResult();
	}
}
