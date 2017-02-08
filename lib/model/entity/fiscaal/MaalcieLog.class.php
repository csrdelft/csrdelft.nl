<?php

class MaalcieLog extends PersistentEntity {
	public $id;
	public $ip;
	public $type;
	public $timestamp;

	protected static $table_name = 'maalcielog';
	protected static $persistent_attributes = array(
		'id' => array(T::Integer, false, 'auto_increment'),
		'ip' => array(T::String),
		'type' => array(T::Enumeration, false, 'LogEnum'),
		'timestamp' => array(T::Timestamp)
	);
	protected static $primary_key = array('id');
}
