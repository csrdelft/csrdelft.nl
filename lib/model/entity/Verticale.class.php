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
	 * @var string
	 */
	public $letter;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Uid van kring-coach
	 * @var string
	 */
	public $kringcoach;
	/**
	 * Uid van verticale-leider
	 * @var string
	 */
	private $leider;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'letter'	 => array(T::Char),
		'naam'		 => array(T::String),
		'kringcoach' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('letter');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	public function getLeider() {
		if (!isset($this->leider)) {
			$this->leider = VerticalenModel::getLeider($this);
		}
		return $this->leider;
	}

}
