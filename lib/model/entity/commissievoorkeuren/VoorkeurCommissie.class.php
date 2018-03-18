<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class VoorkeurCommissie extends PersistentEntity
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $naam;

	/**
	 * @var boolean
	 */
	public $zichtbaar;

	/**
	 * @var integer
	 */
	public $categorie_id;

	/**
	 * @var string
	 */
	protected static $table_name = 'voorkeurCommissie';

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'id' => [T::Integer, false, "auto_increment"],
		'naam' => [T::String, false],
		'zichtbaar' => [T::Boolean, false],
		'categorie_id' => [T::Integer, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];

	public function getCategorie(): VoorkeurCommissieCategorie
	{
		return VoorkeurCommissieCategorieModel::instance()->retrieveByUUID($this->categorie_id);
	}
}
