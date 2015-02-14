<?php

/**
 * Gesprek.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Gesprek extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime last message
	 * @var string
	 */
	public $laatste_update;
	/**
	 * Laatste bericht
	 * @var string
	 */
	public $laatste_bericht;
	/**
	 * Aantal nieuwe berichten sinds laatst gelezen
	 * @var int
	 */
	public $aantal_nieuw;
	/**
	 * Aantal seconden delay
	 * @var int
	 */
	public $auto_update;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'gesprek_id'		 => array(T::Integer, false, 'auto_increment'),
		'laatste_update'	 => array(T::DateTime),
		'laatste_bericht'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('gesprek_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprekken';

	public function getDeelnemers() {
		return GesprekDeelnemersModel::instance()->getDeelnemersVanGesprek($this);
	}

	public function getDeelnemersFormatted() {
		$deelnemers = '';
		foreach ($this->getDeelnemers() as $deelnemer) {
			if ($deelnemer->uid === LoginModel::getUid()) {
				continue;
			}
			if (!empty($deelnemers)) {
				$deelnemers .= ', ';
			}
			$deelnemers .= ProfielModel::get($deelnemer->uid)->getLink();
		}
		return $deelnemers;
	}

	public function getBerichten(GesprekDeelnemer $deelnemer, $timestamp) {
		$timestamp = (int) $timestamp - 1;
		$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
		if ($timestamp < $toegevoegd) {
			$timestamp = $toegevoegd;
		}
		$gelezen = strtotime($deelnemer->gelezen_moment);

		// Auto update
		$max_interval = (int) Instellingen::get('gesprekken', 'max_interval_actief_milisec');
		$min_interval = (int) Instellingen::get('gesprekken', 'min_interval_actief_milisec');
		$actieve_deelnemers = 0;
		foreach ($this->getDeelnemers() as $andere_deelnemer) {
			if ($deelnemer->uid !== $andere_deelnemer->uid AND time() - strtotime($andere_deelnemer->gelezen_moment) < $max_interval) {
				$actieve_deelnemers++;
			}
		}
		if ($actieve_deelnemers > 0) {
			$laatst = max($timestamp, $gelezen);
			$nieuw = $this->getAantalNieuweBerichten($deelnemer, $timestamp);
			if ($nieuw > 0) {
				$this->auto_update = (time() - $laatst) / $nieuw;
			} else {
				$this->auto_update = (time() - $laatst) / $actieve_deelnemers;
			}
			if ($this->auto_update < $min_interval) {
				$this->auto_update = $min_interval;
			}
		} else {
			$this->auto_update = false;
		}

		$deelnemer->gelezen_moment = getDateTime();
		GesprekDeelnemersModel::instance()->update($deelnemer);
		return GesprekBerichtenModel::instance()->getBerichtenSinds($this, $timestamp);
	}

	public function getAantalNieuweBerichten(GesprekDeelnemer $deelnemer, $timestamp) {
		if (!isset($this->aantal_nieuw)) {
			$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
			if ($timestamp < $toegevoegd) {
				$timestamp = $toegevoegd;
			}
			$this->aantal_nieuw = GesprekBerichtenModel::instance()->getAantalBerichtenSinds($this, $timestamp);
		}
		return $this->aantal_nieuw;
	}

}
