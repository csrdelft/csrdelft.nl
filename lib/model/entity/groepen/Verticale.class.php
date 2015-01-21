<?php

/**
 * Verticale.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Verticale extends Groep {

	/**
	 * Letter
	 * @var string
	 */
	public $letter;
	/**
	 * Kringen met kringleden
	 * @var array
	 */
	private $kringen;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'letter' => array(T::Char)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	/**
	 * @deprecated
	 */
	public function getKringen() {
		if (!isset($this->kringen)) {
			$this->kringen = array();
			$kringen = Database::sqlSelect(array('kring, GROUP_CONCAT(uid ORDER BY kringleider ASC, achternaam ASC, voornaam ASC) as kringleden'), 'profielen', 'verticale = ? AND ( status IN (?,?,?,?) OR (status = ? AND kring > 0) )', array($this->letter, LidStatus::Noviet, LidStatus::Lid, LidStatus::Gastlid, LidStatus::Kringel, LidStatus::Oudlid), 'kring', 'kring');
			foreach ($kringen as $result) {
				$kring = $result['kring'];
				$leden = explode(',', $result['kringleden']);
				$this->kringen[$kring] = array();
				foreach ($leden as $uid) {
					$this->kringen[$kring][] = ProfielModel::get($uid);
				}
			}
		}
		return $this->kringen;
	}

	/**
	 * @deprecated
	 */
	public function getKring($kring) {
		$this->getKringen();
		if (!isset($this->kringen[$kring])) {
			return false;
		}
		return $this->kringen[$kring];
	}

}
