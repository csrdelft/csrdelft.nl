<?php

/**
 * Ketzer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ketzer is een aanmeldbare groep.
 * 
 */
class Ketzer extends OpvolgbareGroep {

	/**
	 * Maximaal aantal groepsleden
	 * @var string
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var string
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var string
	 */
	public $aanmelden_tot;
	/**
	 * Bedrag in centen
	 * @var integer
	 */
	public $kosten_bedrag;
	/**
	 * Rekeningnummer voor machtiging 
	 * @var string
	 */
	public $machtiging_rekening;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'aanmeld_limiet'		 => array(T::Integer, true),
		'aanmelden_vanaf'		 => array(T::DateTime),
		'aanmelden_tot'			 => array(T::DateTime),
		'kosten_bedrag'			 => array(T::Integer, true),
		'machtiging_rekening'	 => array(T::String, true)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzers';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return KetzerSelector[]
	 */
	public function getSelectors() {
		return KetzerSelectorsModel::instance()->getSelectorsVoorKetzer($this);
	}

}
