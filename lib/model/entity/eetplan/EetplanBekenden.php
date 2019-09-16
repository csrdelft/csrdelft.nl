<?php

namespace CsrDelft\model\entity\eetplan;

use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class EetplanBekenden extends PersistentEntity {
	public $uid1;
	public $uid2;
	public $opmerking;

	public function getNoviet1() {
		return ProfielModel::instance()->find('uid = ?', array($this->uid1))->fetch();
	}

	public function getNoviet2() {
		return ProfielModel::instance()->find('uid = ?', array($this->uid2))->fetch();
	}

	protected static $table_name = 'eetplan_bekenden';
	protected static $persistent_attributes = array(
		'uid1' => array(T::UID, false),
		'uid2' => array(T::UID, false),
		'opmerking' => [T::String, true],
	);
	protected static $primary_key = array('uid1', 'uid2');
}
