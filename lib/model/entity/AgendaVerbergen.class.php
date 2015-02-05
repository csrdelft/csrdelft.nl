<?php

/**
 * AgendaVerbergen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Items in de agenda kunnen worden verborgen per gebruiker.
 */
class AgendaVerbergen extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $uid;
	/**
	 * Primary key
	 * @var int
	 */
	public $uuid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'	 => array(T::UID),
		'uuid'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'uuid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'agenda_verbergen';

}
