<?php

require_once 'model/entity/groepen/ActiviteitSoort.enum.php';

/**
 * Activiteit.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Activiteit extends OpvolgbareGroep implements Agendeerbaar {

	const leden = 'ActiviteitDeelnemersModel';

	/**
	 * Intern / Extern / SjaarsActie
	 * @var ActiviteitSoort
	 */
	public $soort;
	/**
	 * Locatie
	 * @var string
	 */
	public $locatie;
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
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
	 * Bedrag in centen
	 * @var int
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
		'soort'					 => array(T::Enumeration, false, 'ActiviteitSoort'),
		'locatie'				 => array(T::String, true),
		'rechten_aanmelden'		 => array(T::String, true),
		'aanmeld_limiet'		 => array(T::Integer, true),
		'aanmelden_vanaf'		 => array(T::DateTime),
		'aanmelden_tot'			 => array(T::DateTime),
		'bewerken_tot'			 => array(T::DateTime),
		'afmelden_tot'			 => array(T::DateTime, true),
		'kosten_bedrag'			 => array(T::Integer, true),
		'machtiging_rekening'	 => array(T::String, true)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'activiteiten';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @param string $uid affected Lid
	 * @return boolean
	 */
	public function mag($action, $uid = null) {

		if ($uid === LoginModel::getUid()) {
			/**
			 * Beheerders mogen de volgende eisen standaard negeren, maar als ze zichzelf
			 * bijv. achteraf willen aanmelden moet je A::Beheren ipv A::Aanmelden vragen.
			 */
			if (isset($this->rechten_aanmelden) AND ! LoginModel::mag($this->rechten_aanmelden)) {
				return false;
			}
			switch ($action) {

				case A::Aanmelden:
					if (in_array($uid, $this->getLeden())) {
						return false;
					}
					if (isset($this->aanmeld_limiet) AND $this->aantalLeden() >= $this->aanmeld_limiet) {
						return false;
					}
					return time() < strtotime($this->aanmelden_tot) AND time() > strtotime($this->aanmelden_vanaf);

				case A::Bewerken:
					return time() < strtotime($this->bewerken_tot);

				case A::Afmelden:
					return time() < strtotime($this->afmelden_tot);

				default: // fall-through naar parent::mag
			}
		}
		return parent::mag($action, $uid);
	}

	// Agendeerbaar:

	/**
	 * Timestamp van eindmoment.
	 */
	public function getBeginMoment() {
		return strtotime($this->moment_begin);
	}

	/**
	 * Timestamp van eindmoment.
	 */
	public function getEindMoment() {
		return strtotime($this->moment_einde);
	}

	/**
	 * Tijdstuur in minuten.
	 */
	public function getDuration() {
		return ($this->getEindMoment() - $this->getBeginMoment()) / 60;
	}

	public function getTitel() {
		return $this->naam;
	}

	public function getBeschrijving() {
		return $this->samenvatting;
	}

	public function getLocatie() {
		return $this->locatie;
	}

	public function getLink() {
		return $this->website;
	}

	public function isHeledag() {
		return date('H:i', $this->getBeginMoment()) == '00:00' AND date('H:i', $this->getEindMoment()) == '23:59';
	}

}
