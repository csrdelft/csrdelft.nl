<?php

namespace CsrDelft\repository\agenda;

use CsrDelft\entity\agenda\AgendaVerbergen;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\OrmTrait;
use CsrDelft\model\security\LoginModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AgendaVerbergenRepository extends ServiceEntityRepository {
	use OrmTrait;

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, AgendaVerbergen::class);
	}

	public function toggleVerbergen(Agendeerbaar $item) {
		$verborgen = $this->find(['uid' => LoginModel::getUid(), 'refuuid' => $item->getUUID()]);
		if (!$verborgen) {
			$verborgen = new AgendaVerbergen();
			$verborgen->uid = LoginModel::getUid();
			$verborgen->refuuid = $item->getUUID();
			$this->create($verborgen);
		} else {
			$this->delete($verborgen);
		}
	}

	public function isVerborgen(Agendeerbaar $item) {
		return $this->find(['uid' => LoginModel::getUid(), 'refuuid' => $item->getUUID()]);
	}

}
