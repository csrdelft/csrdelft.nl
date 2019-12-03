<?php

namespace CsrDelft\model\entity\commissievoorkeuren;

use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
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
		return ProfielRepository::get($this->uid);
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
