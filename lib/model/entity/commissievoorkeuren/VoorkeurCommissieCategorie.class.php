<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class VoorkeurCommissieCategorie extends PersistentEntity {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $naam;

	/**
	 * @var string
	 */
	protected static $table_name = 'voorkeurCommissieCategorie';

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'naam' => [T::String, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];

	/**
	 * @return VoorkeurCommissie[]
	 */
	public function getCommissies() {
		return VoorkeurCommissieModel::instance()->find("categorie_id = ?", [$this->id])->fetchAll();
	}
}
