<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

require_once 'model/fiscaat/MaalcieBestellingInhoudModel.class.php';
require_once 'model/entity/fiscaat/MaalcieBestelling.class.php';

class CiviBestelling extends PersistentEntity {
	public $id;
	public $uid;
	public $totaal = 0;
	public $deleted;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	public $inhoud = array();

	public function add(CiviBestellingInhoud $maaltijd) {
		$this->inhoud[] = $maaltijd;
		$maaltijd->bestellingid = $this->id;

		$this->totaal += CiviBestellingInhoudModel::instance()->getPrijs($maaltijd);
	}

	protected static $table_name = 'CiviBestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean)
	);
	protected static $primary_key = array('id');
}
