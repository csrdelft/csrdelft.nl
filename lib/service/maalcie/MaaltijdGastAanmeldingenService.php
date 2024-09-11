<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Functies om de gasten van een maaltijdaanmelding te veranderen.
 */
class MaaltijdGastAanmeldingenService
{
	public function __construct(
		private readonly EntityManagerInterface $entityManager,
		private readonly MaaltijdenRepository $maaltijdenRepository,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
	}

	/**
	 * @param int $mid
	 * @param string $uid
	 * @param int $gasten
	 * @return MaaltijdAanmelding
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function saveGasten($mid, $uid, $gasten)
	{
		if (!is_numeric($mid) || $mid <= 0) {
			throw new CsrGebruikerException(
				'Save gasten faalt: Invalid $mid =' . $mid
			);
		}
		if (!is_numeric($gasten) || $gasten < 0) {
			throw new CsrGebruikerException(
				'Save gasten faalt: Invalid $gasten =' . $gasten
			);
		}
		if (!$this->maaltijdAanmeldingenRepository->getIsAangemeld($mid, $uid)) {
			throw new CsrGebruikerException('Niet aangemeld');
		}

		$maaltijd = $this->maaltijdenRepository->getMaaltijd($mid);
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		$aanmelding = $this->maaltijdAanmeldingenRepository->loadAanmelding(
			$mid,
			$uid
		);
		$verschil = $gasten - $aanmelding->aantal_gasten;
		if (
			$maaltijd->getAantalAanmeldingen() + $verschil >
			$maaltijd->aanmeld_limiet
		) {
			throw new CsrGebruikerException('Maaltijd zit te vol');
		}
		if ($aanmelding->aantal_gasten !== $gasten) {
			$aanmelding->laatst_gewijzigd = date_create_immutable();
		}
		$aanmelding->aantal_gasten = $gasten;
		$this->entityManager->persist($aanmelding);
		$this->entityManager->flush();
		$maaltijd->aantal_aanmeldingen =
			$maaltijd->getAantalAanmeldingen() + $verschil;
		$aanmelding->maaltijd = $maaltijd;
		return $aanmelding;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @param Profiel $profiel
	 * @param string $opmerking
	 * @return MaaltijdAanmelding
	 */
	public function saveGastenEetwens(
		Maaltijd $maaltijd,
		Profiel $profiel,
		$opmerking
	) {
		$aanmelding = $maaltijd->getAanmelding($profiel);
		if (!$aanmelding) {
			throw new CsrGebruikerException('Niet aangemeld');
		}
		if ($maaltijd->gesloten) {
			throw new CsrGebruikerException('Maaltijd is gesloten');
		}
		if ($aanmelding->aantal_gasten <= 0) {
			throw new CsrGebruikerException('Geen gasten aangemeld');
		}
		$aanmelding->gasten_eetwens = $opmerking;
		$this->entityManager->flush();
		return $aanmelding;
	}
}
