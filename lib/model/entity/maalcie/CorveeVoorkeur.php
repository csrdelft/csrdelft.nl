<?php

namespace CsrDelft\model\entity\maalcie;

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

/**
 * CorveeVoorkeur.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 *
 * Een crv_voorkeur instantie beschrijft een voorkeur van een lid om een periodieke taak uit te voeren.
 *
 *
 * Zie ook CorveeRepetitie.class.php
 *
 */
class CorveeVoorkeur extends PersistentEntity {
	# shared primary key

	public $crv_repetitie_id;
	public $uid;
	public $corvee_repetitie;
	public $van_uid;

	public function getCorveeRepetitieId() {
		return (int)$this->crv_repetitie_id;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getVanUid() {
		return $this->van_uid;
	}

	public function getCorveeRepetitie() {
		return $this->corvee_repetitie;
	}

	public function setCorveeRepetitie(CorveeRepetitie $repetitie) {
		$this->corvee_repetitie = $repetitie;
	}

	public function setVanUid($uid) {
		$this->van_uid = $uid;
	}

	protected static $table_name = 'crv_voorkeuren';
	protected static $persistent_attributes = array(
		'uid' => array(T::UID),
		'crv_repetitie_id' => array(T::Integer)
	);

	protected static $primary_key = array('uid', 'crv_repetitie_id');

}
