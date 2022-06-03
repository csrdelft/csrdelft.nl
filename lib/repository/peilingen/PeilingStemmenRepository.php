<?php

namespace CsrDelft\repository\peilingen;

use CsrDelft\entity\peilingen\PeilingStem;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method PeilingStem|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeilingStem|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeilingStem[]    findAll()
 * @method PeilingStem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeilingStemmenRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeilingStem::class);
    }

    /**
     * @param int $peiling_id
     * @param string $uid
     *
     * @return bool
     */
    public function heeftGestemd($peiling_id, $uid)
    {
        return count($this->findBy(['peiling_id' => $peiling_id, 'uid' => $uid])) != 0;
    }

}
