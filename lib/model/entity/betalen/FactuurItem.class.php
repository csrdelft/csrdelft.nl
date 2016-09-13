<?php

/**
 * FactuurItem.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FactuurItem extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $item_id;
	/**
	 * Factuur ID
	 * Foreign key
	 * @var int
	 */
	public $factuur_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Prijs per stuk in centen
	 * @var string
	 */
	public $prijs_per_stuk;
	/**
	 * Aantal stuks
	 * @var int
	 */
	public $aantal;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'item_id'		 => array(T::Integer, false, 'auto_increment'),
		'factuur_id'	 => array(T::Integer),
		'titel'			 => array(T::String),
		'prijs_per_stuk' => array(T::Integer),
		'aantal'		 => array(T::Integer)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('item_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'factuur_items';

}
