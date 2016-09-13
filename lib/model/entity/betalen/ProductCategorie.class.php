<?php

/**
 * ProductCategorie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductCategorie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Rechten benodigd voor beheren
	 * @var string
	 */
	public $beheer_rechten;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'categorie_id'	 => array(T::Integer, false, 'auto_increment'),
		'titel'			 => array(T::String),
		'beheer_rechten' => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('categorie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'product_categorieen';

}
