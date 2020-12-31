<?php

namespace CsrDelft\repository\civimelder;

use CsrDelft\entity\civimelder\Activiteit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Activiteit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activiteit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activiteit[]    findAll()
 * @method Activiteit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiviteitRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Activiteit::class);
	}

	public function getAantalAanmeldingenByActiviteit(Activiteit $activiteit): int {
		$q = $this->createQueryBuilder('a')
        ->select('SUM(a.aantal)')
        ->where('a.activiteit = :activiteit')
        ->setParameter('activiteit', $activiteit)
        ->getQuery();

		try {
			return $q->getSingleScalarResult();
		} catch (NoResultException $e) {
			return 0;
		} catch (NonUniqueResultException $e) {
			return 0;
		}
	}
}
