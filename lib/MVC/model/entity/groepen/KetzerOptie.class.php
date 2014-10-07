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
	 * Optie in deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Optie van deze KetzerSelector
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
	protected static $persistent_attributes = array(
		'ketzer_id' => array(T::Integer),
		'select_id' => array(T::Integer),
		'optie_id' => array(T::Integer),
		'waarde' => array(T::String)
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
	protected static $primary_key = array('ketzer_id', 'select_id', 'optie_id');

}
