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
	 * Dit is een selector van deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Primary key
	 * @var int
	 */
	public $select_id;
	/**
	 * Primary key
	 * @var array
	 */
	public $optie_id;
	/**
	 * Primary key
	 * @var int
	 */
	public $lid_id;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'ketzer_id' => array('int', 11),
		'select_id' => array('int', 11),
		'optie_id' => array('int', 11),
		'lid_id' => array('varchar', 4)
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
	protected static $primary_key = array('ketzer_id', 'select_id', 'optie_id', 'lid_id');

}
