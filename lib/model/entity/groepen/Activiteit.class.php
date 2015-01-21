<?php

require_once 'model/entity/groepen/Ketzer.class.php';

/**
 * Activiteit.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Activiteit extends Ketzer implements Agendeerbaar {

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
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort'		 => array(T::Enumeration, false, 'ActiviteitSoort'),
		'locatie'	 => array(T::String, true)
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
