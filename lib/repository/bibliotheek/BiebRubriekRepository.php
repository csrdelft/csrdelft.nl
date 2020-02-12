<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\BiebRubriek;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BiebRubriek|null find($id, $lockMode = null, $lockVersion = null)
 * @method BiebRubriek|null findOneBy(array $criteria, array $orderBy = null)
 * @method BiebRubriek[]    findAll()
 * @method BiebRubriek[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BiebRubriekRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, BiebRubriek::class);
	}
}
