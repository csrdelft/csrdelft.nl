<?php

/**
 * KetzerKeuze.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een gekozen keuzemogelijkheid door een groeplid.
 * 
 */
class KetzerKeuze extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $lid_id;
	/**
	 * Primary key
	 * @var array
	 */
	public $optie_id;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'lid_id' => 'varchar(4) NOT NULL',
		'optie_id' => 'int(11) NOT NULL'
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_keuzes';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('lid_id', 'optie_id');

}
