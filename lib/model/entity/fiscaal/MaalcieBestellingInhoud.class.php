<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class MaalcieBestellingInhoud extends PersistentEntity {
	public $bestellingid;
	public $productid;
	public $aantal;

	protected static $table_name = 'maalciebestellinginhoud';
	protected static $persistent_attributes = array(
		'bestellingid' => array(T::Integer),
		'productid' => array(T::Integer),
		'aantal' => array(T::Integer)
	);
	protected static $primary_key = array('bestellingid', 'productid');
}
