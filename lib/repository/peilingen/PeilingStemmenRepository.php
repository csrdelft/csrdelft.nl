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

}
