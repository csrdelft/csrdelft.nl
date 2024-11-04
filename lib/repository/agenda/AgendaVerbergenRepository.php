<?php

namespace CsrDelft\repository\agenda;

use CsrDelft\entity\agenda\AgendaVerbergen;
use CsrDelft\entity\agenda\Agendeerbaar;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @package CsrDelft\repository\agenda
 *
 * @method AgendaVerbergen|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaVerbergen|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaVerbergen[]    findAll()
 * @method AgendaVerbergen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaVerbergenRepository extends AbstractRepository
{

}
