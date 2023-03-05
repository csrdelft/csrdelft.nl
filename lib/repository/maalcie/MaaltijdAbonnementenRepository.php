<?php

namespace CsrDelft\repository\maalcie;

use CsrDelft\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\entity\profiel\Profiel;
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

	/**
	 * @param Profiel $lid
	 * @return MaaltijdAbonnement[]
	 */
	public function voorLid(Profiel $lid)
	{
		return $this->findBy(['uid' => $lid->uid]);
	}

	/**
	 * @param Profiel $lid
	 * @return int
	 */
	public function countVoorLid(Profiel $lid)
	{
		return $this->count(['uid' => $lid->uid]);
	}
}
