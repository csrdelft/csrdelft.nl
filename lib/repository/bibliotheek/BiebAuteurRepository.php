<?php

namespace CsrDelft\repository\bibliotheek;

use CsrDelft\entity\bibliotheek\BiebAuteur;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BiebAuteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method BiebAuteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method BiebAuteur[]    findAll()
 * @method BiebAuteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BiebAuteurRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BiebAuteur::class);
    }
}
