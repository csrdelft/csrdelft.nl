<?php

/**
 * StreepLijstProduct.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class StreepLijstProduct extends PersistentEntity {

	/**
	 * Product ID
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $product_id;
	/**
	 * StreepLijst ID
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $streeplijst_id;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'product_id'	 => array(T::Integer),
		'streeplijst_id' => array(T::Integer)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('product_id', 'streeplijst_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'streeplijst_producten';

}
