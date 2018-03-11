<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class VoorkeurCommissie extends PersistentEntity {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $naam;

	/**
	 * @var integer
	 */
	public $zichtbaar;

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
		'zichtbaar' => [T::Boolean, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['id'];
}
