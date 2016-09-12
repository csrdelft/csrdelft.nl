<?php

/**
 * Product.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Product extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $product_id;
	/**
	 * Leverancier ID
	 * Foreign key
	 * @var string
	 */
	public $leverancier_id;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Beschrijving
	 * @var string
	 */
	public $beschrijving;
	/**
	 * Voorraad aantal
	 * @var int
	 */
	public $voorraad;
	/**
	 * DateTime uitverkocht
	 * @var string
	 */
	public $uitverkocht_moment;
	/**
	 * DateTime bevoorrading
	 * @var string
	 */
	public $bevoorrading_moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'product_id'			 => array(T::Integer, false, 'auto_increment'),
		'leverancier_id'		 => array(T::Integer, true),
		'naam'					 => array(T::String),
		'beschrijving'			 => array(T::Text, true),
		'voorraad'				 => array(T::Integer, true),
		'uitverkocht_moment'	 => array(T::DateTime, true),
		'bevoorrading_moment'	 => array(T::DateTime, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('product_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'producten';

}
