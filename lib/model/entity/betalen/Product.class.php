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
	 * ProductCategorie ID
	 * Foreign key
	 * @var int
	 */
	public $categorie_id;
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
	public $aantal_voorraad;
	/**
	 * DateTime uitverkocht
	 * @var string
	 */
	public $uitverkocht_moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'product_id'		 => array(T::Integer, false, 'auto_increment'),
		'categorie_id'		 => array(T::Integer),
		'naam'				 => array(T::String),
		'beschrijving'		 => array(T::Text, true),
		'aantal_voorraad'	 => array(T::Integer, true),
		'uitverkocht_moment' => array(T::DateTime, true)
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
