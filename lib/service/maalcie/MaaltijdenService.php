<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\corvee\CorveeTakenRepository;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class MaaltijdenService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var CorveeTakenRepository
	 */
	private $corveeTakenRepository;
	/**
	 * @var MaaltijdAbonnementenService
	 */
	private $maaltijdAbonnementenService;
	/**
	 * @var MaaltijdAanmeldingenService
	 */
	private $maaltijdAanmeldingenService;

	public function __construct(
		EntityManagerInterface $entityManager,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAbonnementenService $maaltijdAbonnementenService,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		CorveeTakenRepository $corveeTakenRepository
	) {
		$this->entityManager = $entityManager;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->corveeTakenRepository = $corveeTakenRepository;
		$this->maaltijdAbonnementenService = $maaltijdAbonnementenService;
		$this->maaltijdAanmeldingenService = $maaltijdAanmeldingenService;
	}

	/**
	 * @param Maaltijd $maaltijd
	 *
	 * @return array
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function saveMaaltijd($maaltijd)
	{
		$verwijderd = 0;
		if (!$maaltijd->maaltijd_id) {
			$this->entityManager->persist($maaltijd);
			$this->entityManager->flush();
			$this->maaltijdAbonnementenService->meldAboAan($maaltijd);
		} else {
			$this->entityManager->persist($maaltijd);
			$this->entityManager->flush();
			if (
				!$maaltijd->gesloten &&
				$maaltijd->getBeginMoment() < date_create_immutable()
			) {
				$this->maaltijdenRepository->sluitMaaltijd($maaltijd);
			}
			if (
				!$maaltijd->gesloten &&
				!$maaltijd->verwijderd &&
				!empty($maaltijd->filter)
			) {
				$verwijderd = $this->maaltijdAanmeldingenService->checkAanmeldingenFilter(
					$maaltijd->filter,
					[$maaltijd]
				);
				$maaltijd->aantal_aanmeldingen =
					$maaltijd->getAantalAanmeldingen() - $verwijderd;
			}
		}
		return [$maaltijd, $verwijderd];
	}

	/**
	 * @return int
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function prullenbakLeegmaken(): int
	{
		$aantal = 0;
		$maaltijden = $this->maaltijdenRepository->getVerwijderdeMaaltijden();
		foreach ($maaltijden as $maaltijd) {
			try {
				$this->verwijderMaaltijd($maaltijd);
				$aantal++;
			} catch (CsrGebruikerException $e) {
				FlashUtil::setFlashWithContainerFacade($e->getMessage(), -1);
			}
		}
		return $aantal;
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function verwijderMaaltijd(Maaltijd $maaltijd)
	{
		// delete corveetaken first (foreign key)
		$this->corveeTakenRepository->verwijderMaaltijdCorvee(
			$maaltijd->maaltijd_id
		);
		if ($maaltijd->verwijderd) {
			if (
				$this->corveeTakenRepository->existMaaltijdCorvee(
					$maaltijd->maaltijd_id
				)
			) {
				throw new CsrGebruikerException(
					'Er zitten nog bijbehorende corveetaken in de prullenbak. Verwijder die eerst definitief!'
				);
			}
			$this->maaltijdAanmeldingenRepository->deleteAanmeldingenVoorMaaltijd(
				$maaltijd->maaltijd_id
			);
			$this->entityManager->remove($maaltijd);
			$this->entityManager->flush();
		} else {
			$maaltijd->verwijderd = true;
			$this->entityManager->persist($maaltijd);
			$this->entityManager->flush();
		}
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
	private function filterMaaltijdenVoorLid(
		$maaltijden,
		Profiel $profiel,
		$verbergVerleden = false
	): array {
		$result = [];
		foreach ($maaltijden as $maaltijd) {
			// Verberg afgelopen maaltijd
			if (
				$verbergVerleden &&
				$maaltijd->getEindMoment() < date_create_immutable()
			) {
				continue;
			}

			// Kan en mag aanmelden of mag maaltijdlijst zien en sluiten? Dan maaltijd ook zien.
			if (
				($maaltijd->aanmeld_limiet > 0 &&
					$this->maaltijdAanmeldingenService->checkAanmeldFilter(
						$profiel,
						$maaltijd->aanmeld_filter
					)) ||
				$maaltijd->magBekijken($profiel->uid)
			) {
				$result[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		return $result;
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
	public function getMaaltijdenVoorAgenda($van, $tot)
	{
		if (!is_int($van)) {
			throw new CsrException(
				'Invalid timestamp: $van getMaaltijdenVoorAgenda()'
			);
		}
		if (!is_int($tot)) {
			throw new CsrException(
				'Invalid timestamp: $tot getMaaltijdenVoorAgenda()'
			);
		}

		// Zet de tijd naar 00:00, omdat maaltijden apart de tijd opslaan
		$van_datum_0000 = date_create_immutable("@$van")->setTime(0, 0, 0, 0);
		$tot_datum_0000 = date_create_immutable("@$tot")->setTime(0, 0, 0, 0);

		$maaltijden = $this->maaltijdenRepository->getMaaltijdenTussen(
			$van_datum_0000,
			$tot_datum_0000
		);

		$maaltijden = $this->filterMaaltijdenVoorLid(
			$maaltijden,
			LoginService::getProfiel()
		);
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden op die beschikbaar zijn voor aanmelding voor het lid in de ingestelde periode vooraf.
	 *
	 * @param string $uid
	 *
	 * @return Maaltijd[]
	 */
	public function getKomendeMaaltijdenVoorLid(Profiel $profiel)
	{
		$maaltijden = $this->maaltijdenRepository->getMaaltijdenTussen(
			date_create('-1 day'),
			date_create(
				InstellingUtil::instelling('maaltijden', 'toon_ketzer_vooraf')
			)
		);

		$maaltijden = $this->filterMaaltijdenVoorLid($maaltijden, $profiel, true);
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijd op die in een ketzer zal worden weergegeven.
	 *
	 * @param int $mid
	 * @return Maaltijd|false
	 */
	public function getMaaltijdVoorKetzer($mid)
	{
		$maaltijden = [$this->maaltijdenRepository->getMaaltijd($mid)];
		$maaltijden = $this->filterMaaltijdenVoorLid(
			$maaltijden,
			LoginService::getProfiel()
		);
		if (!empty($maaltijden)) {
			return reset($maaltijden);
		}
		return false;
	}
}
