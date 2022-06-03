<?php

namespace CsrDelft\repository;

use CsrDelft\entity\WoordVanDeDag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WoordVanDeDag|null find($id, $lockMode = null, $lockVersion = null)
 * @method WoordVanDeDag|null findOneBy(array $criteria, array $orderBy = null)
 * @method WoordVanDeDag[]    findAll()
 * @method WoordVanDeDag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WoordVanDeDagRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, WoordVanDeDag::class);
	}
}
