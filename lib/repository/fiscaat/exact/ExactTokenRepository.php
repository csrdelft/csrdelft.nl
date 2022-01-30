<?php

namespace CsrDelft\repository\fiscaat\exact;

use CsrDelft\entity\fiscaat\exact\ExactToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExactToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExactToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExactToken[]    findAll()
 * @method ExactToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExactTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExactToken::class);
    }

    // /**
    //  * @return ExactToken[] Returns an array of ExactToken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ExactToken
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
