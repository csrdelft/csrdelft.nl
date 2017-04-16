<?php

use CsrDelft\Orm\Entity\PersistentEntity;
use CsrDelft\Orm\Entity\T;

class CiviProduct extends PersistentEntity {
	public $id;
	public $status;
	public $beschrijving;
	public $prioriteit;
	public $beheer;

	public $prijs;

	public function getBeschrijving() {
		return sprintf("%s (€%.2f)", $this->beschrijving, $this->prijs/100);
	}

	protected static $table_name = 'CiviProduct';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'status' => array(T::Integer),
		'beschrijving' => array(T::Text),
		'prioriteit' => array(T::Integer),
		'beheer' => array(T::Boolean)
	);
	protected static $primary_key = array('id');
}
