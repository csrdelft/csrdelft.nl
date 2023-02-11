<?php

namespace CsrDelft\service\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\Util\FlashUtil;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;
use CsrDelft\repository\maalcie\MaaltijdRepetitiesRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Throwable;

class MaaltijdAbonnementenService
{
	/**
	 * @var MaaltijdRepetitiesRepository
	 */
	private $maaltijdRepetitiesRepository;
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;
	/**
	 * @var MaaltijdAbonnementenRepository
	 */
	private $maaltijdAbonnementenRepository;
	/**
	 * @var MaaltijdRepetitieAanmeldingenService
	 */
	private $maaltijdRepetitieAanmeldingenService;

	public function __construct(
		EntityManagerInterface $entityManager,
		MaaltijdAbonnementenRepository $maaltijdAbonnementenRepository,
		MaaltijdRepetitiesRepository $maaltijdRepetitiesRepository,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository,
		MaaltijdRepetitieAanmeldingenService $maaltijdRepetitieAanmeldingenService,
		ProfielRepository $profielRepository
	) {
		$this->maaltijdRepetitiesRepository = $maaltijdRepetitiesRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->profielRepository = $profielRepository;
		$this->entityManager = $entityManager;
		$this->maaltijdAbonnementenRepository = $maaltijdAbonnementenRepository;
		$this->maaltijdRepetitieAanmeldingenService = $maaltijdRepetitieAanmeldingenService;
	}

	/**
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function getAbonnementenWaarschuwingenMatrix()
	{
		return $this->entityManager->wrapInTransaction(function () {
			$abos = $this->maaltijdAbonnementenRepository->findAll();

			$waarschuwingen = [];

			foreach ($abos as $abo) {
				$repetitie = $this->maaltijdRepetitiesRepository->getRepetitie(
					$abo->mlt_repetitie_id
				);
				if (
					!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
						$abo->uid,
						$repetitie->abonnement_filter
					)
				) {
					$abo->foutmelding =
						'Niet toegestaan vanwege aanmeldrestrictie: ' .
						$repetitie->abonnement_filter;
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (!$repetitie->abonneerbaar) {
					$abo->foutmelding = 'Niet abonneerbaar';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (
					!LidStatus::isLidLike(ProfielRepository::get($abo->uid)->status)
				) {
					$abo->waarschuwing = 'Geen huidig lid';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			$repById = $this->maaltijdRepetitiesRepository->getAlleRepetities(true);

			return $this->fillHoles($waarschuwingen, $repById);
		});
	}

	/**
	 * @return array
	 * @throws Throwable
	 */
	public function getAbonnementenAbonneerbaarMatrix()
	{
		return $this->entityManager->wrapInTransaction(function () {
			$repById = $this->maaltijdRepetitiesRepository->getAlleRepetities(true); // grouped by mrid

			/** @var Profiel[] $leden */
			$leden = $this->profielRepository
				->createQueryBuilder('p')
				->where('p.status in (:lidstatus)')
				->setParameter('lidstatus', LidStatus::getLidLike())
				->getQuery()
				->getResult();

			$matrix = [];
			foreach ($leden as $lid) {
				$abos = $this->maaltijdAbonnementenRepository->findBy([
					'uid' => $lid->uid,
				]);
				foreach ($abos as $abo) {
					$rep = $repById[$abo->mlt_repetitie_id];
					if (
						!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
							$lid->uid,
							$rep->abonnement_filter
						)
					) {
						$abo->foutmelding =
							'Niet toegestaan vanwege aanmeldrestrictie: ' .
							$rep->abonnement_filter;
					}
					$matrix[$lid->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			return $this->fillHoles($matrix, $repById);
		});
	}

	/**
	 * @param $matrix
	 * @param $repById
	 * @param bool $ingeschakeld
	 * @return array
	 */
	private function fillHoles($matrix, $repById, $ingeschakeld = false)
	{
		foreach ($repById as $mrid => $repetitie) {
			// vul gaten in matrix vanwege uitgeschakelde abonnementen
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
		return [$matrix, $repById];
	}

	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 *
	 * @return MaaltijdAbonnement[][] 2d matrix met eerst uid, en dan repetitie id
	 * @throws Throwable
	 */
	public function getAbonnementenMatrix()
	{
		return $this->entityManager->wrapInTransaction(function () {
			/** @var MaaltijdRepetitie[] $repById */
			$repById = $this->maaltijdRepetitiesRepository->getAlleRepetities(true); // grouped by mrid

			$profielen = $this->profielRepository->findAll();

			$matrix = [];
			foreach ($profielen as $profiel) {
				// Skip oudleden
				if (
					!LidStatus::isLidLike($profiel->status) &&
					$this->maaltijdAbonnementenRepository->count([
						'uid' => $profiel->uid,
					]) == 0
				) {
					continue;
				}

				$matrix[$profiel->uid] = [];

				foreach ($repById as $rep) {
					$abo = $this->maaltijdAbonnementenRepository->find([
						'uid' => $profiel->uid,
						'mlt_repetitie_id' => $rep->mlt_repetitie_id,
					]);

					if (!$abo) {
						$abo = new MaaltijdAbonnement();
						$abo->mlt_repetitie_id = $rep->mlt_repetitie_id;
						$abo->maaltijd_repetitie = $rep;
					} elseif (!$rep->abonneerbaar) {
						$abo->foutmelding = 'Niet abonneerbaar';
					} elseif (!LidStatus::isLidLike($profiel->status)) {
						$abo->foutmelding = 'Geen huidig lid';
					} elseif (
						!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
							$profiel->uid,
							$rep->abonnement_filter
						)
					) {
						$abo->foutmelding =
							'Niet toegestaan vanwege aanmeldrestrictie: ' .
							$rep->abonnement_filter;
					}

					$matrix[$profiel->uid][$rep->mlt_repetitie_id] = $abo;
				}
			}

			return [$matrix, $repById];
		});
	}

	/**
	 * @return MaaltijdAbonnement[][]
	 * @throws Throwable
	 */
	public function getAbonnementenVanNovieten()
	{
		return $this->entityManager->wrapInTransaction(function () {
			$novieten = $this->profielRepository->findBy([
				'status' => LidStatus::Noviet,
			]);
			$matrix = [];
			foreach ($novieten as $noviet) {
				$matrix[$noviet->uid] = $this->maaltijdAbonnementenRepository->findBy(
					['uid' => $noviet->uid],
					['mlt_repetitie_id' => 'DESC']
				);
			}
			return $matrix;
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
	public function verwijderAbonnementenVoorLid($uid)
	{
		return $this->entityManager->transactional(function () use ($uid) {
			$abos = $this->getAbonnementenVoorLid($uid);
			$aantal = 0;
			foreach ($abos as $abo) {
				$aantal++;
				$this->entityManager->remove($abo);
			}
			$this->entityManager->flush();

			if (sizeof($abos) !== $aantal) {
				FlashUtil::setFlashWithContainerFacade(
					'Niet alle abonnementen zijn uitgeschakeld!',
					-1
				);
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
	public function getAbonnementenVoorLid(
		$uid,
		$abonneerbaar = false,
		$uitgeschakeld = false
	) {
		$lijst = $this->entityManager->wrapInTransaction(function () use (
			$uid,
			$abonneerbaar,
			$uitgeschakeld
		) {
			$lijst = [];

			if ($abonneerbaar) {
				$repById = $this->maaltijdRepetitiesRepository->getAbonneerbareRepetitiesVoorLid(
					$uid
				); // grouped by mrid
			} else {
				$repById = $this->maaltijdRepetitiesRepository->getAlleRepetities(true); // grouped by mrid
			}
			$abos = $this->maaltijdAbonnementenRepository->findBy(['uid' => $uid]);
			foreach ($abos as $abo) {
				// ingeschakelde abonnementen
				$mrid = $abo->mlt_repetitie_id;
				if (!array_key_exists($mrid, $repById)) {
					// ingeschakelde abonnementen altijd weergeven
					$repById[$mrid] = $this->maaltijdRepetitiesRepository->getRepetitie(
						$mrid
					);
				}
				$abo->maaltijd_repetitie = $repById[$mrid];
				$abo->van_uid = $uid;
				$lijst[$mrid] = $abo;
			}
			if ($uitgeschakeld) {
				foreach ($repById as $repetitie) {
					$mrid = $repetitie->mlt_repetitie_id;
					if (!array_key_exists($mrid, $lijst)) {
						// uitgeschakelde abonnementen weergeven
						$abo = new MaaltijdAbonnement();
						$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
						$abo->maaltijd_repetitie = $repetitie;
						$abo->van_uid = $uid;
						$lijst[$mrid] = $abo;
					}
				}
			}
			ksort($lijst);

			return $lijst;
		});

		if ($lijst === true) {
			return [];
		}

		return $lijst;
	}

	/**
	 * @param $abo MaaltijdAbonnement
	 * @return false|int
	 * @throws CsrGebruikerException
	 * @throws Throwable
	 */
	public function inschakelenAbonnement($abo)
	{
		return $this->entityManager->transactional(function () use ($abo) {
			if (!$abo->maaltijd_repetitie->abonneerbaar) {
				throw new CsrGebruikerException('Niet abonneerbaar');
			}
			if (
				$this->maaltijdAbonnementenRepository->find([
					'mlt_repetitie_id' => $abo->mlt_repetitie_id,
					'uid' => $abo->uid,
				])
			) {
				throw new CsrGebruikerException('Abonnement al ingeschakeld');
			}
			if (
				!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
					$abo->uid,
					$abo->maaltijd_repetitie->abonnement_filter
				)
			) {
				throw new CsrGebruikerException(
					'Niet toegestaan vanwege aanmeldrestrictie: ' .
						$abo->maaltijd_repetitie->abonnement_filter
				);
			}

			$abo->van_uid = $abo->uid;
			$abo->wanneer_ingeschakeld = date_create_immutable();
			$this->entityManager->persist($abo);
			$this->entityManager->flush();

			return $this->maaltijdRepetitieAanmeldingenService->aanmeldenVoorKomendeRepetitieMaaltijden(
				$abo->maaltijd_repetitie,
				$abo->uid
			);
		});
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function inschakelenAbonnementVoorNovieten(
		MaaltijdRepetitie $repetitie
	) {
		return $this->entityManager->wrapInTransaction(function () use (
			$repetitie
		) {
			$novieten = $this->profielRepository->findBy([
				'status' => LidStatus::Noviet,
			]);

			$aantal = 0;
			foreach ($novieten as $noviet) {
				if (
					!$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
						$noviet->uid,
						$repetitie->abonnement_filter
					)
				) {
					continue;
				}

				$abo = new MaaltijdAbonnement();
				$abo->maaltijd_repetitie = $repetitie;
				$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
				$abo->uid = $noviet->uid;
				$abo->wanneer_ingeschakeld = date_create_immutable();

				if (
					$this->maaltijdAbonnementenRepository->find([
						'mlt_repetitie_id' => $abo->mlt_repetitie_id,
						'uid' => $abo->uid,
					])
				) {
					continue;
				}
				$this->entityManager->persist($abo);
				$this->maaltijdRepetitieAanmeldingenService->aanmeldenVoorKomendeRepetitieMaaltijden(
					$repetitie,
					$noviet->uid
				);
				$aantal += 1;
			}

			$this->entityManager->flush();

			return $aantal;
		});
	}

	/**
	 * @param MaaltijdRepetitie $repetitie
	 * @param $uid
	 * @return bool|mixed
	 * @throws Throwable
	 */
	public function uitschakelenAbonnement(MaaltijdRepetitie $repetitie, $uid)
	{
		return $this->entityManager->wrapInTransaction(function () use (
			$repetitie,
			$uid
		) {
			if (
				!$this->maaltijdAbonnementenRepository->getHeeftAbonnement(
					$repetitie,
					$uid
				)
			) {
				throw new CsrGebruikerException('Abonnement al uitgeschakeld');
			}

			$abo = $this->maaltijdAbonnementenRepository->getAbonnement(
				$repetitie,
				$uid
			);
			$rep = $abo->maaltijd_repetitie;
			$this->entityManager->remove($abo);
			$this->entityManager->flush();

			$abo = new MaaltijdAbonnement();
			$abo->maaltijd_repetitie = $repetitie;
			$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
			$abo->maaltijd_repetitie = $rep;
			$abo->van_uid = $uid;

			$aantal = $this->maaltijdAanmeldingenRepository->afmeldenDoorAbonnement(
				$repetitie,
				$uid
			);
			return [$abo, $aantal];
		});
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
	public function verwijderAbonnementen(MaaltijdRepetitie $mrid)
	{
		return $this->entityManager->wrapInTransaction(function () use ($mrid) {
			$abos = $this->maaltijdAbonnementenRepository->getAbonnementenVoorRepetitie(
				$mrid
			);
			$aantal = count($abos);
			foreach ($abos as $abo) {
				$this->maaltijdAanmeldingenRepository->afmeldenDoorAbonnement(
					$mrid,
					$abo->uid
				);
				$this->entityManager->remove($abo);
			}
			$this->entityManager->flush();
			return $aantal;
		});
	}

	/**
	 * @param Maaltijd $maaltijd
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function meldAboAan($maaltijd)
	{
		$aantal = 0;
		// aanmelden van leden met abonnement op deze repetitie
		if (!$maaltijd->gesloten && $maaltijd->repetitie !== null) {
			$abonnementen = $this->maaltijdAbonnementenRepository->getAbonnementenVoorRepetitie(
				$maaltijd->repetitie
			);
			foreach ($abonnementen as $abo) {
				if (
					$this->maaltijdAanmeldingenRepository->checkAanmeldFilter(
						$abo->uid,
						$maaltijd->aanmeld_filter
					)
				) {
					if (
						$this->maaltijdRepetitieAanmeldingenService->aanmeldenDoorAbonnement(
							$maaltijd,
							$abo->maaltijd_repetitie,
							$abo->uid
						)
					) {
						$aantal++;
					}
				}
			}
		}
		$maaltijd->aantal_aanmeldingen = $aantal;
	}
}
