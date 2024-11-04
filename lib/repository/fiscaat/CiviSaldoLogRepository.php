<?php

namespace CsrDelft\repository\fiscaat;

use CsrDelft\entity\fiscaat\CiviSaldoLog;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method CiviSaldoLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CiviSaldoLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CiviSaldoLog[]    findAll()
 * @method CiviSaldoLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CiviSaldoLogRepository extends AbstractRepository
{

}
