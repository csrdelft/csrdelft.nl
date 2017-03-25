<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class MaalcieBestelling extends PersistentEntity {
	public $id;
	public $uid;
	public $totaal;
	public $deleted;

	public $inhoud;

	protected static $table_name = 'maalciebestelling';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'uid' => array(T::UID),
		'totaal' => array(T::Integer),
		'deleted' => array(T::Boolean)
	);
	protected static $primary_key = array('id');
}
