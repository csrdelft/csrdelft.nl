<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\model\entity\commissievoorkeuren\VoorkeurVoorkeur;
use CsrDelft\model\entity\Profiel;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use SplObjectStorage;

class VoorkeurCommissieModel extends PersistenceModel {

	const ORM = VoorkeurCommissie::class;

	public function getByCategorie() {

		$categorien = VoorkeurCommissieCategorieModel::instance()->find();
		$cat2commissie = [];
		foreach ($categorien as $cat) {
			$cat2commissie[$cat->id] = ['categorie' => $cat, 'commissies' => []];
		}

		$commissies = $this->find(null, [], null, "naam");

		foreach ($commissies as $commissie) {
			$cat2commissie[$commissie->categorie_id]['commissies'][] = $commissie;

		}
		return $cat2commissie;

	}

}
