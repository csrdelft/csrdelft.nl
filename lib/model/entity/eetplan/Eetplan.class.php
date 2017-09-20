<?php

namespace CsrDelft\model\entity\eetplan;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\model\entity\Profiel;
use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class Eetplan extends PersistentEntity {
	/**
	 * @var string
	 */
	public $uid;

	/**
	 * @var int
	 */
	public $woonoord_id;

	/**
	 * @var string
	 */
	public $avond;

	/**
	 * @return Woonoord|false|mixed
	 */
	public function getWoonoord() {
		return WoonoordenModel::get($this->woonoord_id);
	}

	/**
	 * @return Profiel|false
	 */
	public function getNoviet() {
		return ProfielModel::get($this->uid);
	}

	/**
	 * @var string
	 */
	protected static $table_name = 'eetplan';

	/**
	 * @var array
	 */
	protected static $persistent_attributes = [
		'uid' => [T::UID, false],
		'woonoord_id' => [T::Integer, false],
		'avond' => [T::Date, false]
	];

	/**
	 * @var string[]
	 */
	protected static $primary_key = ['uid', 'woonoord_id'];
}
