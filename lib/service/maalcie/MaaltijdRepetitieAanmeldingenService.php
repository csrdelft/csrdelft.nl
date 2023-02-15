<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAanmelding;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\repository\ProfielRepository;
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
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
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
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService
	) {
		$this->entityManager = $entityManager;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
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
		$uid
	) {
		if (
			!$this->maaltijdAanmeldingenService->checkAanmeldFilter(
				$uid,
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
			if (
				!$this->maaltijdAanmeldingenRepository->find([
					'maaltijd_id' => $maaltijd->maaltijd_id,
					'uid' => $uid,
				])
			) {
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
	public function aanmeldenDoorAbonnement(
		Maaltijd $maaltijd,
		MaaltijdRepetitie $repetitie,
		$uid
	) {
		if (
			!$this->maaltijdAanmeldingenRepository->find([
				'maaltijd_id' => $maaltijd->maaltijd_id,
				'uid' => $uid,
			])
		) {
			try {
				$this->maaltijdAanmeldingenService->assertMagAanmelden($maaltijd, $uid);

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
	 * @param string $uid
	 * @return MaaltijdRepetitie[]
	 * @internal param MaaltijdRepetitie[] $repetities
	 */
	public function getAbonneerbareRepetitiesVoorLid($uid)
	{
		$repetities = $this->maaltijdRepetitiesRepository->getAbboneerbareRepetities();
		$result = [];
		foreach ($repetities as $repetitie) {
			if (
				$this->maaltijdAanmeldingenService->checkAanmeldFilter(
					$uid,
					$repetitie->abonnement_filter
				)
			) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
		}
		return $result;
	}
}
