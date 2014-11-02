<?php

/**
 * LidInstelling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een LidInstelling beschrijft een Instelling per Lid.
 * 
 * @see Instelling.class.php
 */
class LidInstelling extends Instelling {

	/**
	 * Uid
	 * @var string
	 */
	public $uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'uid' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('module', 'instelling_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lidinstellingen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	/**
	 * Cast values to defined type.
	 * 
	 * @param boolean $attributes Attributes to cast
	 */
	protected function castValues(array $attributes) {
		parent::castValues($attributes);
		if (LidInstellingen::instance()->getType($this->module, $this->instelling_id) === T::Integer) {
			$this->waarde = (int) $this->waarde;
		}
	}

}
