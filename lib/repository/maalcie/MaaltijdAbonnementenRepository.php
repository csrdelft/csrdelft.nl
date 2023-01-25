<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
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
class MaaltijdAbonnementenRepository extends AbstractRepository
{
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(
		ManagerRegistry $registry,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		parent::__construct($registry, MaaltijdAbonnement::class);
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	public function getAbonnementenVoorRepetitie(MaaltijdRepetitie $repetitie)
	{
		return $this->findBy(['maaltijd_repetitie' => $repetitie]);
	}

	/**
	 * @param $abo MaaltijdAbonnement
	 * @return false|int
	 * @throws CsrGebruikerException
	 * @throws Throwable
	 */
	public function inschakelenAbonnement($abo)
	{
		return $this->_em->transactional(function () use ($abo) {
			if (!$abo->maaltijd_repetitie->abonneerbaar) {
				throw new CsrGebruikerException('Niet abonneerbaar');
			}
			if (
				$this->find([
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
			$this->_em->persist($abo);
			$this->_em->flush();

			return $this->maaltijdAanmeldingenRepository->aanmeldenVoorKomendeRepetitieMaaltijden(
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
		return $this->_em->transactional(function () use ($repetitie) {
			$novieten = ContainerFacade::getContainer()
				->get(ProfielRepository::class)
				->findBy(['status' => LidStatus::Noviet]);

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
					$this->find([
						'mlt_repetitie_id' => $abo->mlt_repetitie_id,
						'uid' => $abo->uid,
					])
				) {
					continue;
				}
				$this->_em->persist($abo);
				$this->maaltijdAanmeldingenRepository->aanmeldenVoorKomendeRepetitieMaaltijden(
					$repetitie,
					$noviet->uid
				);
				$aantal += 1;
			}

			$this->_em->flush();

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
		return $this->_em->transactional(function () use ($repetitie, $uid) {
			if (!$this->getHeeftAbonnement($repetitie->mlt_repetitie_id, $uid)) {
				throw new CsrGebruikerException('Abonnement al uitgeschakeld');
			}

			$abo = $this->find([
				'mlt_repetitie_id' => $repetitie->mlt_repetitie_id,
				'uid' => $uid,
			]);
			$rep = $abo->maaltijd_repetitie;
			$this->_em->remove($abo);
			$this->_em->flush();

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

	public function getHeeftAbonnement($mrid, $uid)
	{
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
	public function verwijderAbonnementen(MaaltijdRepetitie $mrid)
	{
		return $this->_em->transactional(function () use ($mrid) {
			$abos = $this->findBy(['maaltijd_repetitie' => $mrid]);
			$aantal = count($abos);
			foreach ($abos as $abo) {
				$this->maaltijdAanmeldingenRepository->afmeldenDoorAbonnement(
					$mrid,
					$abo->uid
				);
				$this->_em->remove($abo);
			}
			$this->_em->flush();
			return $aantal;
		});
	}
}
