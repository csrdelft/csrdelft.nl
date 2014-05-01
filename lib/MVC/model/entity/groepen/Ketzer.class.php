<?php

/**
 * Ketzer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ketzer is een aanmeldbare opvolgbare groep.
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
	 * Ketzer-selectors
	 * @var KetzerSelector[]
	 */
	private $ketzer_selectors;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'aanmeld_limiet' => array('int', 11, true),
		'aanmelden_vanaf' => array('datetime'),
		'aanmelden_tot' => array('datetime')
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzers';

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
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
