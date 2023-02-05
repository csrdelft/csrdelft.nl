<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

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
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, MaaltijdAbonnement::class);
	}

	public function getAbonnementenVoorRepetitie(MaaltijdRepetitie $repetitie)
	{
		return $this->findBy(['maaltijd_repetitie' => $repetitie]);
	}

	public function getAbonnement(MaaltijdRepetitie $maaltijdRepetitie, $uid)
	{
		return $this->find([
			'mlt_repetitie_id' => $maaltijdRepetitie->mlt_repetitie_id,
			'uid' => $uid,
		]);
	}

	public function getHeeftAbonnement(MaaltijdRepetitie $maaltijdRepetitie, $uid)
	{
		return $this->getAbonnement($maaltijdRepetitie, $uid) != null;
	}
}
