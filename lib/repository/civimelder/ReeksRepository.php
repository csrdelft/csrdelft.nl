<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\entity\civimelder\Reeks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reeks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reeks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reeks[]    findAll()
 * @method Reeks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReeksRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Reeks::class);
	}
}
