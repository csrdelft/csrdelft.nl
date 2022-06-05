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
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AgendaVerbergen::class);
	}

	public function toggleVerbergen(Agendeerbaar $item)
	{
		$verborgen = $this->find([
			'uid' => LoginService::getUid(),
			'refuuid' => $item->getUUID(),
		]);
		if (!$verborgen) {
			$verborgen = new AgendaVerbergen();
			$verborgen->uid = LoginService::getUid();
			$verborgen->refuuid = $item->getUUID();
			$this->save($verborgen);
		} else {
			$this->remove($verborgen);
		}
	}

	public function isVerborgen(Agendeerbaar $item)
	{
		return $this->find([
			'uid' => LoginService::getUid(),
			'refuuid' => $item->getUUID(),
		]);
	}
}
