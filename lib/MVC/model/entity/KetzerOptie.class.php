<?php

/**
 * KetzerOptie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een soort ketzeroptie heeft keuzemogelijkheden.
 * 
 */
class KetzerOptie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $optie_id;
	/**
	 * Dit is een optie van deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Checkbox (AND) / Radio (XOR)
	 * @see KetzerOptieSoort
	 * @var string
	 */
	public $keuze_soort;
	/**
	 * Mogelijke waarden als keuze
	 * @var array
	 */
	public $keuzemogelijkheden;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'optie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'ketzer_id' => 'int(11) NOT NULL',
		'keuze_soort' => 'varchar(3) NOT NULL',
		'keuzemogelijkheden' => 'text NOT NULL'
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzeropties';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('optie_id', 'ketzer_id');

}
