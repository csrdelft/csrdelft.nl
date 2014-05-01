<?php

/**
 * KetzerOptie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een keuzemogelijkheid van een ketzer kan gekozen worden door een groeplid.
 * 
 */
class KetzerOptie extends PersistentEntity {

	/**
	 * Optie van deze ketzerselector
	 * @var int
	 */
	public $select_id;
	/**
	 * Primary key
	 * @var int
	 */
	public $optie_id;
	/**
	 * Keuzewaarde
	 * @var string
	 */
	public $waarde;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'select_id' => array('int', 11),
		'optie_id' => array('int', 11),
		'waarde' => array('string', 255)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_opties';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_keys = array('select_id', 'optie_id');

}
