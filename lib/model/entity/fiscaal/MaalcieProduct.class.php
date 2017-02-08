<?php

class MaalcieProduct extends PersistentEntity {
	public $id;
	public $status;
	public $beschrijving;
	public $prioriteit;
	public $beheer;

	protected static $table_name = 'maalcieproduct';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'status' => array(T::Integer),
		'beschrijving' => array(T::Text),
		'prioriteit' => array(T::Integer),
		'beheer' => array(T::Boolean)
	);
	protected static $primary_key = array('id');
}
