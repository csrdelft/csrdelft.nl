<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class MaaltijdRepetitieAanmeldingenService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var MaaltijdenRepository
	 */
	private $maaltijdenRepository;
	/**
	 * @var MaaltijdAanmeldingenService
	 */
	private $maaltijdAanmeldingenService;
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;

	public function __construct(
		EntityManagerInterface $entityManager,
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService
	) {
		$this->entityManager = $entityManager;
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdAanmeldingenService = $maaltijdAanmeldingenService;
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
	}

	/**
	 * Alleen aanroepen voor inschakelen abonnement!
	 *
	 * @param MaaltijdRepetitie $repetitie
	 * @param string $uid
	 * @return int|false aantal aanmeldingen or false
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function aanmeldenVoorKomendeRepetitieMaaltijden(
		MaaltijdRepetitie $repetitie,
		Profiel $profiel
	) {
		if (
			!$this->maaltijdAanmeldingenService->checkAanmeldFilter(
				$profiel,
				$repetitie->abonnement_filter
			)
		) {
			throw new CsrGebruikerException(
				'Niet toegestaan vanwege aanmeldrestrictie: ' .
					$repetitie->abonnement_filter
			);
		}

		$aantal = 0;

		/** @var Maaltijd[] $maaltijden */
		$maaltijden = $this->maaltijdenRepository
			->createQueryBuilder('m')
			->where(
				'm.mlt_repetitie_id = :repetitie and m.gesloten = false and m.verwijderd = false and m.datum >= :datum'
			)
			->setParameter('repetitie', $repetitie->mlt_repetitie_id)
			->setParameter('datum', date_create())
			->orderBy('m.datum', 'ASC')
			->addOrderBy('m.tijd', 'ASC')
			->getQuery()
			->getResult();

		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->getAanmelding($profiel)) {
				if ($this->aanmeldenDoorAbonnement($maaltijd, $repetitie, $profiel)) {
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
	public function aanmeldenDoorAbonnement(
		Maaltijd $maaltijd,
		MaaltijdRepetitie $repetitie,
		Profiel $profiel
	) {
		if (!$maaltijd->getAanmelding($profiel)) {
			try {
				$this->maaltijdAanmeldingenService->assertMagAanmelden(
					$maaltijd,
					$profiel
				);

				$aanmelding = new MaaltijdAanmelding();
				$aanmelding->maaltijd = $maaltijd;
				$aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
				$aanmelding->uid = $profiel->uid;
				$aanmelding->profiel = $profiel;
				$aanmelding->door_uid = $profiel->uid;
				$aanmelding->door_profiel = $profiel;
				$aanmelding->abonnementRepetitie = $repetitie;
				$aanmelding->laatst_gewijzigd = date_create_immutable();
				$aanmelding->gasten_eetwens = '';

				$this->entityManager->persist($aanmelding);
				$this->entityManager->flush();

				return true;
			} catch (CsrGebruikerException $e) {
				return false;
			}
		}

		return false;
	}

	/**
	 * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
	 *
	 * @param Profiel $profiel
	 * @return MaaltijdRepetitie[]
	 * @internal param MaaltijdRepetitie[] $repetities
	 */
	public function getAbonneerbareRepetitiesVoorLid(Profiel $profiel)
	{
		$repetities = $this->maaltijdRepetitiesRepository->getAbboneerbareRepetities();
		$result = [];
		foreach ($repetities as $repetitie) {
			if (
				$this->maaltijdAanmeldingenService->checkAanmeldFilter(
					$profiel,
					$repetitie->abonnement_filter
				)
			) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
		}
		return $result;
	}
}
