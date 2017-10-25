<?php

namespace CsrDelft\model\agenda;

use CsrDelft\model\entity\agenda\AgendaVerbergen;
use CsrDelft\model\entity\agenda\Agendeerbaar;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;

class AgendaVerbergenModel extends PersistenceModel {

	const ORM = AgendaVerbergen::class;

	public function toggleVerbergen(Agendeerbaar $item) {
		$verborgen = $this->retrieveByPrimaryKey(array(LoginModel::getUid(), $item->getUUID()));
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
		return $this->existsByPrimaryKey(array(LoginModel::getUid(), $item->getUUID()));
	}

}
