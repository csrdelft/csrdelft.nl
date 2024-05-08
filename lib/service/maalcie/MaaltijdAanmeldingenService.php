<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\fiscaat\CiviBestelling;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\fiscaat\CiviSaldoRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class MaaltijdAanmeldingenService
{
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var CiviSaldoRepository
	 */
	private $civiSaldoRepository;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var AccessService
	 */
	private $accessService;

	public function __construct(
		EntityManagerInterface $entityManager,
		AccessService $accessService,
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		CiviSaldoRepository $civiSaldoRepository
	) {
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->entityManager = $entityManager;
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
		if (
			!$maaltijd->gesloten &&
			$maaltijd->getBeginMoment() < date_create_immutable()
		) {
			$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			$this->assertMagAanmelden($maaltijd, $profiel);
		}

		$aanmelding = $maaltijd->getAanmelding($profiel);
		if ($aanmelding) {
			if (!$beheer) {
				throw new CsrGebruikerException('Al aangemeld');
			}
			$verschil = $aantalGasten - $aanmelding->aantal_gasten;
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->laatst_gewijzigd = date_create_immutable();
			$this->entityManager->persist($aanmelding);
			$this->entityManager->flush();
			$maaltijd->aantal_aanmeldingen =
				$maaltijd->getAantalAanmeldingen() + $verschil;
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
			$this->entityManager->persist($aanmelding);
			$this->entityManager->flush();

			$maaltijd->aantal_aanmeldingen =
				$maaltijd->getAantalAanmeldingen() + 1 + $aantalGasten;
		}
		$aanmelding->maaltijd = $maaltijd;
		return $aanmelding;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param string $uid
	 * @throws CsrGebruikerException
	 */
	public function assertMagAanmelden(Maaltijd $maaltijd, Profiel $profiel)
	{
		if (!$this->civiSaldoRepository->getSaldo($profiel->uid)) {
			throw new CsrGebruikerException(
				'Aanmelden voor maaltijden niet toegestaan, geen CiviSaldo.'
			);
		}
		if (!$this->checkAanmeldFilter($profiel, $maaltijd->aanmeld_filter)) {
			throw new CsrGebruikerException(
				'Niet toegestaan vanwege aanmeldrestrictie: ' .
					$maaltijd->aanmeld_filter
			);
		}
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet) {
			throw new CsrGebruikerException('Maaltijd zit al vol');
		}
	}

	/**
	 * @param Profiel $profiel
	 * @param string $filter
	 * @return bool Of de gebruiker voldoet aan het filter
	 */
	public function checkAanmeldFilter(Profiel $profiel, $filter)
	{
		$account = $profiel->account;
		if (!$account) {
			throw new CsrGebruikerException(
				'Lid bestaat niet: $uid =' . $profiel->uid
			);
		}
		if (empty($filter)) {
			return true;
		}
		return $this->accessService->isUserGranted($account, $filter);
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param Profiel $profiel
	 * @param bool $beheer
	 * @return Maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function afmeldenDoorLid(
		Maaltijd $maaltijd,
		Profiel $profiel,
		$beheer = false
	): Maaltijd {
		$aanmelding = $maaltijd->getAanmelding($profiel);
		if (!$aanmelding) {
			throw new CsrGebruikerException('Niet aangemeld');
		}
		if (
			!$maaltijd->gesloten &&
			$maaltijd->getBeginMoment() < date_create_immutable()
		) {
			$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$this->entityManager->remove($aanmelding);
		$this->entityManager->flush();
		$maaltijd->aantal_aanmeldingen =
			$maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->aantal_gasten;
		return $maaltijd;
	}

	public function getRecenteAanmeldingenVoorLid(
		$uid,
		DateTimeInterface $timestamp
	) {
		$maaltijdenById = $this->maaltijdenRepository->getRecenteMaaltijden(
			$timestamp
		);
		return $this->maaltijdAanmeldingenRepository->getAanmeldingenVoorLid(
			$maaltijdenById,
			$uid
		);
	}

	public function maakCiviBestelling(MaaltijdAanmelding $aanmelding): CiviBestelling
	{
		$bestelling = new CiviBestelling();
		$bestelling->cie = $aanmelding->maaltijd->product->categorie->cie;
		$bestelling->uid = $aanmelding->uid;
		$bestelling->civiSaldo = $this->civiSaldoRepository->find($aanmelding->uid);
		$bestelling->deleted = false;
		$bestelling->moment = new DateTime();
		$bestelling->comment = sprintf(
			'Datum maaltijd: %s',
			$aanmelding->maaltijd->getBeginMoment()->format('Y-M-d')
		);

		$inhoud = new CiviBestellingInhoud();
		$inhoud->aantal = 1 + $aanmelding->aantal_gasten;
		$inhoud->product_id = $aanmelding->maaltijd->product_id;
		$inhoud->product = $aanmelding->maaltijd->product;

		$bestelling->inhoud[] = $inhoud;
		$bestelling->totaal =
			$aanmelding->maaltijd->product->getPrijsInt() *
			(1 + $aanmelding->aantal_gasten);

		return $bestelling;
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
	public function checkAanmeldingenFilter($filter, $maaltijden)
	{
		$maaltijdenFiltered = [];
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$maaltijdenFiltered[] = $maaltijd;
			}
		}
		if (empty($maaltijdenFiltered)) {
			return 0;
		}
		$aantal = 0;
		/** @var MaaltijdAanmelding[] $aanmeldingen */
		$aanmeldingen = [];
		foreach ($maaltijdenFiltered as $maaltijd) {
			$aanmeldingen = array_merge(
				$aanmeldingen,
				$this->maaltijdAanmeldingenRepository->findVoorMaaltijd($maaltijd)
			);
		}
		foreach ($aanmeldingen as $aanmelding) {
			// check filter voor elk aangemeld lid
			if (!$this->checkAanmeldFilter($aanmelding->profiel, $filter)) {
				// verwijder aanmelding indien niet toegestaan
				$aantal += 1 + $aanmelding->aantal_gasten;
				$this->entityManager->remove($aanmelding);
			}
		}
		$this->entityManager->flush();
		return $aantal;
	}
}
