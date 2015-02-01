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

	const leden = 'KetzerDeelnemersModel';

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
	 * Datum en tijd aanmelding bewerken toegestaan
	 * @var string
	 */
	public $bewerken_tot;
	/**
	 * Datum en tijd afmelden toegestaan
	 * @var string
	 */
	public $afmelden_tot;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'aanmeld_limiet'	 => array(T::Integer, true),
		'aanmelden_vanaf'	 => array(T::DateTime),
		'aanmelden_tot'		 => array(T::DateTime),
		'bewerken_tot'		 => array(T::DateTime),
		'afmelden_tot'		 => array(T::DateTime, true)
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

	public function getUrl() {
		return '/groepen/ketzers/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action, $ical = false) {
		// parent checks specifieke en algemene rechten
		if (!parent::mag($action, $ical)) {
			return false;
		}
		$aangemeld = array_key_exists(LoginModel::getUid(), $this->getLeden());

		switch ($action) {

			case A::Aanmelden:
				if ($aangemeld) {
					return false;
				}
				// Controleer maximum leden
				if (isset($this->aanmeld_limiet) AND $this->aantalLeden() >= $this->aanmeld_limiet) {
					return false;
				}
				// Controleer aanmeldperiode
				return time() < strtotime($this->aanmelden_tot) AND time() > strtotime($this->aanmelden_vanaf);

			case A::Bewerken:
				if (!$aangemeld) {
					return false;
				}
				// Controleer bewerkperiode
				return time() < strtotime($this->bewerken_tot);

			case A::Afmelden:
				if (!$aangemeld) {
					return false;
				}
				// Controleer afmeldperiode
				return time() < strtotime($this->afmelden_tot);

			default:
				// Parent is al gechecked
				return true;
		}
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
