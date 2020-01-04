<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class VoorkeurOpmerking extends PersistentEntity {
	/**
	 * @var string
	 */
	public $uid;

	/**
	 * @var string
	 */
	public $lidOpmerking;

	/**
	 * @var string
	 */
	public $praesesOpmerking;

	/**
	 * @var string
	 */
	protected static $table_name = 'voorkeurOpmerking';

	/**
	 * @return Profiel
	 */
	public function getProfiel() {
		return ProfielModel::get($this->uid);
	}

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'uid' => [T::UID, false],
		'lidOpmerking' => [T::Text, true],
		'praesesOpmerking' => [T::Text, true]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['uid'];
}
