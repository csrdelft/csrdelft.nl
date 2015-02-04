<?php

require_once 'model/entity/groepen/ActiviteitSoort.enum.php';
require_once 'model/entity/groepen/Ketzer.class.php';

/**
 * Activiteit.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Activiteit extends Ketzer implements Agendeerbaar {

	const leden = 'ActiviteitDeelnemersModel';

	/**
	 * Intern / Extern / SjaarsActie / etc.
	 * @var ActiviteitSoort
	 */
	public $soort;
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Locatie
	 * @var string
	 */
	public $locatie;
	/**
	 * Tonen in agenda
	 * @var boolean
	 */
	public $in_agenda;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort'				 => array(T::Enumeration, false, 'ActiviteitSoort'),
		'rechten_aanmelden'	 => array(T::String, true),
		'locatie'			 => array(T::String, true),
		'in_agenda'			 => array(T::Boolean)
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

	public function getUrl() {
		return '/groepen/activiteiten/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		switch ($action) {

			case A::Bekijken:
				if (LoginModel::mag('P_LEDEN_MOD')) {
					return true;
				}

			case A::Aanmelden:
				if (!empty($this->rechten_aanmelden) AND ! LoginModel::mag($this->rechten_aanmelden)) {
					return false;
				}
		}
		return parent::mag($action);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 * 
	 * @param AccessAction $action
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $soort = null) {
		// Beheer over commissie-ketzers bij betreffende commissie
		switch ($soort) {
			case ActiviteitSoort::OWee: return LoginModel::mag('P_LEDEN_MOD,commissie:OWeeCie');
			case ActiviteitSoort::Dies: return LoginModel::mag('P_LEDEN_MOD,commissie:DiesCie');
			case ActiviteitSoort::Lustrum: return LoginModel::mag('P_LEDEN_MOD,commissie:LustrumCie');
		}
		return parent::magAlgemeen($action);
	}

	// Agendeerbaar:

	public function getBeginMoment() {
		return strtotime($this->begin_moment);
	}

	public function getEindMoment() {
		if ($this->eind_moment AND $this->eind_moment !== $this->begin_moment) {
			return strtotime($this->eind_moment);
		}
		return $this->getBeginMoment() + 1800;
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
		return $this->getUrl();
	}

	public function isHeledag() {
		$begin = date('H:i', $this->getBeginMoment());
		$eind = date('H:i', $this->getEindMoment());
		return $begin == '00:00' AND ( $eind == '23:59' OR $eind == '00:00' );
	}

}
