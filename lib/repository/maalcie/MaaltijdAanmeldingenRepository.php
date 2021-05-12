<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(ManagerRegistry $registry, CiviSaldoRepository $civiSaldoRepository, AccessService $accessService) {
		parent::__construct($registry, MaaltijdAanmelding::class);
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->accessService = $accessService;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param Profiel $profiel
	 * @param Profiel $doorProfiel
	 * @param int $aantalGasten
	 * @param bool $beheer
	 * @param string $gastenEetwens
	 * @return MaaltijdAanmelding|null
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmeldenVoorMaaltijd(
		Maaltijd $maaltijd,
		Profiel $profiel,
		Profiel $doorProfiel,
		$aantalGasten = 0,
		$beheer = false,
		$gastenEetwens = ''
	) {
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			$this->assertMagAanmelden($maaltijd, $profiel->uid);
		}

		if ($maaltijd->getIsAangemeld($profiel->uid)) {
			if (!$beheer) {
				throw new CsrGebruikerException('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasten door beheerder
			$aanmelding = $this->loadAanmelding($maaltijd->maaltijd_id, $profiel->uid);
			$verschil = $aantalGasten - $aanmelding->aantal_gasten;
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->laatst_gewijzigd = date_create_immutable();
			$this->getEntityManager()->persist($aanmelding);
			$this->getEntityManager()->flush();
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
		} else {
			$aanmelding = new MaaltijdAanmelding();
			$aanmelding->maaltijd = $maaltijd;
			$aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
			$aanmelding->uid = $profiel->uid;
			$aanmelding->profiel = $profiel;
			$aanmelding->door_uid = $doorProfiel->uid;
			$aanmelding->door_profiel = $profiel;
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
		if (!ContainerFacade::getContainer()->get(CiviSaldoRepository::class)->getSaldo($uid)) {
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
		$account = AccountRepository::get($uid); // false if account does not exist
		if (!$account) {
			throw new CsrGebruikerException('Lid bestaat niet: $uid =' . $uid);
		}
		if (empty($filter)) {
			return true;
		}
		return $this->accessService->mag($account, $filter);
	}

	public function getIsAangemeld($mid, $uid) {
		return $this->find(['maaltijd_id' => $mid, 'uid' => $uid]) != null;
	}

	/**
	 * @param $mid
	 * @param $uid
	 * @return MaaltijdAanmelding
	 */
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
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid Lid voor wie het MaaltijdAbonnement wordt uitschakeld
	 *
	 * @return int|null
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmeldenDoorAbonnement(MaaltijdRepetitie $repetitie, $uid) {
		// afmelden bij maaltijden waarbij dit abonnement de aanmelding heeft gedaan
		$maaltijden = ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->getKomendeOpenRepetitieMaaltijden($repetitie->mlt_repetitie_id);
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
			if ($aanmelding->abonnementRepetitie && $repetitie->mlt_repetitie_id === $aanmelding->abonnementRepetitie->mlt_repetitie_id) {
				$this->getEntityManager()->remove($aanmelding);
				$aantal++;
			}
		}
		$this->getEntityManager()->flush();
		return $aantal;
	}

	/**
	 * @param $maaltijdenById
	 * @param $uid
	 * @return MaaltijdAanmelding[]
	 */
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

	/**
	 * @param Maaltijd $maaltijd
	 * @param Profiel $profiel
	 * @param bool $beheer
	 * @return Maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmeldenDoorLid(Maaltijd $maaltijd, Profiel $profiel, $beheer = false) {
		if (!$this->getIsAangemeld($maaltijd->maaltijd_id, $profiel->uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
			ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$aanmelding = $this->loadAanmelding($maaltijd->maaltijd_id, $profiel->uid);
		$this->_em->remove($aanmelding);
		$this->_em->flush();
		$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->aantal_gasten;
		return $maaltijd;
	}

	/**
	 * @param int $mid
	 * @param string $uid
	 * @param int $gasten
	 * @return MaaltijdAanmelding
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
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

		$maaltijd = ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->getMaaltijd($mid);
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

	/**
	 * @param int $mid
	 * @param string $uid
	 * @param string $opmerking
	 * @return MaaltijdAanmelding
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function saveGastenEetwens($mid, $uid, $opmerking) {
		if (!is_numeric($mid) || $mid <= 0) {
			throw new CsrGebruikerException('Save gasten eetwens faalt: Invalid $mid =' . $mid);
		}
		$maaltijd = ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->getMaaltijd($mid);
		if (!$maaltijd->getIsAangemeld($uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}

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
			$naam = $aanmelding->profiel->getNaam('streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->aantal_gasten; $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->door_uid = $aanmelding->profiel->uid;
				$gast->door_profiel = $aanmelding->profiel;
				$lijst[$naam . 'gast' . $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public function getRecenteAanmeldingenVoorLid($uid, DateTimeInterface $timestamp) {
		$maaltijdenById = ContainerFacade::getContainer()->get(MaaltijdenRepository::class)->getRecenteMaaltijden($timestamp);
		return $this->getAanmeldingenVoorLid($maaltijdenById, $uid);
	}

	/**
	 * Called when a Maaltijd is being deleted.
	 *
	 * @param int $mid maaltijd-id
	 * @throws ORMException
	 * @throws OptimisticLockException
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
	 * @throws ORMException
	 * @throws OptimisticLockException
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
		$bestelling->cie = $aanmelding->maaltijd->product->categorie->cie;
		$bestelling->uid = $aanmelding->uid;
		$bestelling->civiSaldo = $this->civiSaldoRepository->find($aanmelding->uid);
		$bestelling->deleted = false;
		$bestelling->moment = new DateTime();
		$bestelling->comment = sprintf('Datum maaltijd: %s', date('Y-M-d', $aanmelding->maaltijd->getBeginMoment()));

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->product_id = $aanmelding->maaltijd->product_id;
		$inhoud->product = $aanmelding->maaltijd->product;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal = $aanmelding->maaltijd->product->getPrijsInt() * (1 + $aanmelding->aantal_gasten);

		return $bestelling;
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * Alleen aanroepen voor inschakelen abonnement!
	 *
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return int|false aantal aanmeldingen or false
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmeldenVoorKomendeRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $uid) {
		if (!$this->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
			throw new CsrGebruikerException('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
		}

		$aantal = 0;
		$maaltijdenRepository = ContainerFacade::getContainer()->get(MaaltijdenRepository::class);

		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $maaltijdenRepository->createQueryBuilder('m')
			->where('m.mlt_repetitie_id = :repetitie and m.gesloten = false and m.verwijderd = false and m.datum >= :datum')
			->setParameter('repetitie', $repetitie->mlt_repetitie_id)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()->getResult();

		foreach ($maaltijden as $maaltijd) {
			if (!$this->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => $uid])) {
				if ($this->aanmeldenDoorAbonnement($maaltijd, $repetitie, $uid)) {
					$aantal++;
				}
			}
		}
		return $aantal;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return bool
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmeldenDoorAbonnement(Maaltijd $maaltijd, MaaltijdRepetitie $repetitie, $uid) {
		if (!$this->find(['maaltijd_id' => $maaltijd->maaltijd_id, 'uid' => $uid])) {
			try {
				$this->assertMagAanmelden($maaltijd, $uid);

				$profiel = ProfielRepository::get($uid);
				$aanmelding = new MaaltijdAanmelding();
				$aanmelding->maaltijd = $maaltijd;
				$aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
				$aanmelding->uid = $uid;
				$aanmelding->profiel = $profiel;
				$aanmelding->door_uid = $uid;
				$aanmelding->door_profiel = $profiel;
				$aanmelding->abonnementRepetitie = $repetitie;
				$aanmelding->laatst_gewijzigd = date_create_immutable();
				$aanmelding->gasten_eetwens = '';

				$this->_em->persist($aanmelding);
				$this->_em->flush();

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
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function getAantalAanmeldingen(Maaltijd $maaltijd) {
		return $this->createQueryBuilder('maaltijd_aanmelding')
			->select('SUM(maaltijd_aanmelding.aantal_gasten) + COUNT(maaltijd_aanmelding.uid)')
			->where('maaltijd_aanmelding.maaltijd_id = :maaltijd_id')
			->setParameter('maaltijd_id', $maaltijd->maaltijd_id)
			->getQuery()->getSingleScalarResult();
	}
}
