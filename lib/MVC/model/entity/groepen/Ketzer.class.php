<?php

/**
 * Ketzer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ketzer is een aanmeldbare groep.
 * 
 */
class Ketzer extends Groep {

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
	 * Bedrag dat mag worden afgeschreven
	 * @var float
	 */
	public $kosten_bedrag;
	/**
	 * Tegenrekening van machtiging 
	 * @var string
	 */
	public $machtiging_rekening;
	/**
	 * Ketzer-selectors
	 * @var KetzerSelector[]
	 */
	private $ketzer_selectors;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'aanmeld_limiet'		 => array(T::Integer, true),
		'aanmelden_vanaf'		 => array(T::DateTime),
		'aanmelden_tot'			 => array(T::DateTime),
		'kosten_bedrag'			 => array(T::Float, true),
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
	public function getKetzerSelectors() {
		if (!isset($this->ketzer_selectors)) {
			$this->setKetzerSelectors(KetzerSelectorsModel::instance()->getSelectorsVoorKetzer($this));
		}
		return $this->ketzer_selectors;
	}

	public function hasKetzerSelectors() {
		return count($this->getKetzerSelectors()) > 0;
	}

	public function setKetzerSelectors(array $selectors) {
		$this->ketzer_selectors = $selectors;
	}

}
