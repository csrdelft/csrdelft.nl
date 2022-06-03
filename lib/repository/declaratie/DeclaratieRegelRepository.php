<?php

namespace CsrDelft\repository\declaratie;

use CsrDelft\entity\declaratie\DeclaratieRegel;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeclaratieRegel|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclaratieRegel|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclaratieRegel[]    findAll()
 * @method DeclaratieRegel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclaratieRegelRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeclaratieRegel::class);
    }
}
