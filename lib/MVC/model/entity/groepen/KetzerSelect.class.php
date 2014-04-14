<?php

/**
 * KetzerSelect.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een soort selector (AND/XOR) heeft keuzemogelijkheden.
 * 
 */
class KetzerSelect extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $select_id;
	/**
	 * Dit is een selector van deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Checkbox (AND) / Radio (XOR)
	 * @see KetzerSelectSoort
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
		'select_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'ketzer_id' => 'int(11) NOT NULL',
		'keuze_soort' => 'varchar(3) NOT NULL',
		'keuzemogelijkheden' => 'text NOT NULL'
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_selectors';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('select_id');

}
