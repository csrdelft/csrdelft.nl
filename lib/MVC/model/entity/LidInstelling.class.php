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
	public $lid_id;
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
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'lid_id' => array('string', 4),
		'module' => array('string', 255),
		'instelling_id' => array('string', 255),
		'waarde' => array('string', 255)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('lid_id', 'module', 'instelling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lidinstellingen';

}
