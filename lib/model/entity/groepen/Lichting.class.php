<?php

/**
 * Lichting.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * TODO: extend Groep
 */
class Lichting extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $lidjaar;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lidjaar' => array(T::Char)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('lidjaar');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lichtingen';

	public function getUrl() {
		return '/groepen/lichtingen/' . $this->lidjaar . '/';
	}

}
