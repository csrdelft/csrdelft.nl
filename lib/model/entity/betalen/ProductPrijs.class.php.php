<?php

/**
 * ProductPrijs.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductPrijs extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $prijs_id;
	/**
	 * ProductPrijsLijst ID
	 * Foreign key
	 * @var int
	 */
	public $lijst_id;
	/**
	 * Product ID
	 * Foreign key
	 * @var int
	 */
	public $product_id;
	/**
	 * Prijs in centen
	 * @var int
	 */
	public $prijs;
	/**
	 * DateTime begin
	 * @var string
	 */
	public $begin_moment;
	/**
	 * DateTime eind
	 * @var string
	 */
	public $eind_moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'prijs_id'		 => array(T::Integer, false, 'auto_increment'),
		'lijst_id'		 => array(T::Integer),
		'product_id'	 => array(T::Integer),
		'prijs'			 => array(T::Integer),
		'begin_moment'	 => array(T::DateTime),
		'eind_moment'	 => array(T::DateTime, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('prijs_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'product_prijzen';

}
