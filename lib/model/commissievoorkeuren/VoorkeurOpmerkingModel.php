<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\model\entity\commissievoorkeuren\VoorkeurOpmerking;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\PersistenceModel;

class VoorkeurOpmerkingModel extends PersistenceModel {

	const ORM = VoorkeurOpmerking::class;

	/**
	 * @param Profiel $profiel
	 * @return VoorkeurOpmerking
	 */
	public function getOpmerkingVoorLid(Profiel $profiel) {
		$result = $this->retrieveByPrimaryKey([$profiel->uid]);
		if ($result == false) {
			$result = new VoorkeurOpmerking();
			$result->uid = $profiel->uid;
		}
		return $result;
	}

	/**
	 * Updates the model if it exists,
	 * otherwise creates it.
	 * @TODO remove this function when implemented in ORM
	 * @param PersistentEntity $entity
	 * @return boolean whether a new row was created
	 */
	public function updateOrCreate(PersistentEntity $entity) {
		if ($this->exists($entity)) {
			$this->update($entity);
			return true;
		} else {
			$this->create($entity);
			return false;
		}
	}

}
