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
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(ManagerRegistry $registry, SerializerInterface $serializer)
    {
        parent::__construct($registry, CiviSaldoLog::class);
        $this->serializer = $serializer;
    }

    /**
     * @param string $type
     * @param string $data
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function log($type, $data)
    {
        $logEntry = new CiviSaldoLog();
        // Don't use filter_input for $_SERVER when PHP runs through FastCGI:
        // https://bugs.php.net/bug.php?id=49184
        $logEntry->ip = isset($_SERVER['REMOTE_ADDR']) ? filter_var($_SERVER['REMOTE_ADDR']) : '';
        $logEntry->type = $type;
        $logEntry->data = $this->serializer->serialize($data, 'json', ['groups' => ['log']]);
        $logEntry->timestamp = date_create_immutable();
        $this->_em->persist($logEntry);
        $this->_em->flush();
    }
}
