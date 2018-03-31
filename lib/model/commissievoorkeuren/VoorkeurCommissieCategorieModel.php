<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\Orm\PersistenceModel;

class VoorkeurCommissieCategorieModel extends PersistenceModel {

	const ORM = VoorkeurCommissieCategorie::class;

	public function getAll() {
		$result = [];
		foreach ($this->find() as $cat) {
			$result[$cat->id] = $cat;
		}
		return $result;
	}
}
