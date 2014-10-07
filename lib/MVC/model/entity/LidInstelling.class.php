<?php

/**
 * LidInstelling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een instelling instantie beschrijft een key-value pair voor een module.
 * 
 * Bijvoorbeeld:
 * 
 * Voor maaltijden-module:
 *  - Standaard maaltijdprijs
 *  - Marge in verband met gasten
 * 
 * Voor corvee-module:
 *  - Corveepunten per jaar
 * 
 */
class LidInstelling extends PersistentEntity {

	/**
	 * Uid
	 * @var string
	 */
	public $uid;
	/**
	 * Module
	 * @var string
	 */
	public $module;
	/**
	 * Key
	 * @var string
	 */
	public $instelling_id;
	/**
	 * Value
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid'			 => array(T::UID),
		'module'		 => array(T::String),
		'instelling_id'	 => array(T::String),
		'waarde'		 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('uid', 'module', 'instelling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lidinstellingen';

}
