<?php

namespace CsrDelft\model\commissievoorkeuren;

use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;

class VoorkeurCommissieModel extends PersistenceModel {

	const ORM = VoorkeurCommissie::class;
	/**
	 * @var VoorkeurCommissieCategorieModel
	 */
	private $voorkeurCommissieCategorieModel;

	public function __construct(VoorkeurCommissieCategorieModel $voorkeurCommissieCategorieModel) {
		parent::__construct();
		$this->voorkeurCommissieCategorieModel = $voorkeurCommissieCategorieModel;
	}

	public function getByCategorie() {

		$categorien = $this->voorkeurCommissieCategorieModel->find();
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
