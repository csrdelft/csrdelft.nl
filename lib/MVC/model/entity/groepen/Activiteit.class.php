<?php

require_once 'MVC/model/entity/groepen/Ketzer.class.php';

/**
 * Activiteit.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Activiteit extends Ketzer implements Agendeerbaar {

	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'activiteiten';

	// Agendeerbaar:

	public function getUID() {
		return $this->groep_id . '@' . static::$table_name . '.csrdelft.nl';
	}

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

	public function getLink() {
		return $this->website;
	}

	public function isHeledag() {
		return date('H:i', $this->getBeginMoment()) == '00:00' AND date('H:i', $this->getEindMoment()) == '23:59';
	}

}
