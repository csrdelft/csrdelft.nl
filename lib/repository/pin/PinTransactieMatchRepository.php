<?php

namespace CsrDelft\repository\pin;

use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 23/02/2018
 *
 * @method PinTransactieMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method PinTransactieMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method PinTransactieMatch[]    findAll()
 * @method PinTransactieMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method PinTransactieMatch|null retrieveByUuid($UUID)
 */
class PinTransactieMatchRepository extends AbstractRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PinTransactieMatch::class);
    }

    /**
     * @return PinTransactieMatch[]
     */
    public function metFout()
    {
        return $this->createQueryBuilder('m')
            ->where('m.status != \'match\' and m.status != \'verwijderd\'')
            ->getQuery()->getResult();
    }

    /**
     * @param int[] $ids
     */
    public function cleanByBestellingIds($ids)
    {
        $this->createQueryBuilder('m')
            ->delete()
            ->where('m.bestelling_id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->execute();
    }

    /**
     * @param int[] $ids
     */
    public function cleanByTransactieIds($ids)
    {
        $this->createQueryBuilder('m')
            ->delete()
            ->where('m.transactie_id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->execute();
    }
}
