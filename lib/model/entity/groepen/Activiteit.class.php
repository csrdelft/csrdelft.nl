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
		'soort'		 => array(T::Enumeration, false, 'ActiviteitSoort'),
		'locatie'	 => array(T::String, true),
		'in_agenda'	 => array(T::Boolean)
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
	public function mag($action, $ical = false) {
		if ($action === A::Bekijken AND in_array($this->soort, array(ActiviteitSoort::Extern, ActiviteitSoort::OWee, ActiviteitSoort::IFES))) {
			return true;
		}
		return parent::mag($action, $ical);
	}

	// Agendeerbaar:

	public function getBeginMoment() {
		return strtotime($this->begin_moment);
	}

	public function getEindMoment() {
		if ($this->eind_moment) {
			return strtotime($this->eind_moment);
		}
		return $this->getBeginMoment();
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
