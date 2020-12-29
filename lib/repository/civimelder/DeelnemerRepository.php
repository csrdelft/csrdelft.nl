<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\Entity\civimelder\Deelnemer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Deelnemer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Deelnemer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Deelnemer[]    findAll()
 * @method Deelnemer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeelnemerRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Deelnemer::class);
	}
}
