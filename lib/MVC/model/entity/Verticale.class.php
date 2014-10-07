<?php

/**
 * Verticale.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Verticale extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Letter
	 * @var string
	 */
	public $letter;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Uid of verticale-leider
	 * @var string
	 */
	private $leider;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'	 => array(T::Integer),
		'letter' => array(T::Char),
		'naam'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	public function getLeider() {
		if (!isset($this->leider)) {
			$this->leider = VerticalenModel::instance()->getVerticaleLeider($this);
		}
		return $this->leider;
	}

}
