<?php

namespace CsrDelft\repository;

use CsrDelft\entity\StekPakket;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class StekPakketRepository
 * @package CsrDelft\repository\
 * @method StekPakket|null find($id, $lockMode = null, $lockVersion = null)
 * @method StekPakket|null findOneBy(array $criteria, array $orderBy = null)
 * @method StekPakket[]    findAll()
 * @method StekPakket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StekPakketRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, StekPakket::class);
	}

	/**
	 * @param Profiel $profiel
	 * @return StekPakket|null
	 */
	public function getStekPakketVoorLid(Profiel $profiel) {
		return $this->findOneBy(['uid' => $profiel->uid]);
	}
}
