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

	public function __construct(
		EntityManagerInterface $entityManager,
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		CiviSaldoRepository $civiSaldoRepository
	) {
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->civiSaldoRepository = $civiSaldoRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->entityManager = $entityManager;
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
			$this->assertMagAanmelden($maaltijd, $profiel->uid);
		}

		if ($maaltijd->getIsAangemeld($profiel->uid)) {
			if (!$beheer) {
				throw new CsrGebruikerException('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasten door beheerder
			$aanmelding = $this->maaltijdAanmeldingenRepository->loadAanmelding(
				$maaltijd->maaltijd_id,
				$profiel->uid
			);
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
	public function assertMagAanmelden(Maaltijd $maaltijd, $uid)
	{
		if (!$this->civiSaldoRepository->getSaldo($uid)) {
			throw new CsrGebruikerException(
				'Aanmelden voor maaltijden niet toegestaan, geen CiviSaldo.'
			);
		}
		if (
			!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
				$uid,
				$maaltijd->aanmeld_filter
			)
		) {
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
	) {
		if (
			!$this->maaltijdAanmeldingenRepository->getIsAangemeld(
				$maaltijd->maaltijd_id,
				$profiel->uid
			)
		) {
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
		$aanmelding = $this->maaltijdAanmeldingenRepository->loadAanmelding(
			$maaltijd->maaltijd_id,
			$profiel->uid
		);
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

	public function maakCiviBestelling(MaaltijdAanmelding $aanmelding)
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
}
