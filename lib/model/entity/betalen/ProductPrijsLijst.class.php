<?php

/**
 * ProductPrijsLijst.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductPrijsLijst extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $lijst_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lijst_id'	 => array(T::Integer, false, 'auto_increment'),
		'titel'		 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('lijst_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'product_prijslijsten';

}
