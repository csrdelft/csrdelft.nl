<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

require_once 'model/fiscaat/CiviBestellingInhoudModel.class.php';
require_once 'model/entity/fiscaat/CiviBestelling.class.php';

class CiviBestelling extends PersistentEntity {
	public $id;
	public $uid;
	public $totaal = 0;
	public $deleted;
	public $moment;

	/**
	 * @var CiviBestellingInhoud[]
	 */
	public $inhoud = array();

	protected static $table_name = 'CiviBestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean),
		'moment' => array(T::Timestamp)
	);
	protected static $primary_key = array('id');
}
