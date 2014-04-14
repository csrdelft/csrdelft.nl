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
	 * Primary key
	 * @var int
	 */
	public $optie_id;
	/**
	 * Optie van deze ketzerselector
	 * @var int
	 */
	public $select_id;
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
		'optie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'select_id' => 'int(11) NOT NULL',
		'waarde' => 'varchar(255) NOT NULL'
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
	protected static $primary_key = array('optie_id');

}
