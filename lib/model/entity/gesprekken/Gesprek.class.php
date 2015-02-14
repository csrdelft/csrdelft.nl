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

	public function getBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!is_int($lastUpdate)) {
			throw new Exception('lastUpdate invalid');
		}
		$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
		if ($lastUpdate < $toegevoegd) {
			$lastUpdate = $toegevoegd;
		}
		// Auto update
		$max_interval = (int) Instellingen::get('gesprekken', 'max_interval_actief_milisec');
		$min_interval = (int) Instellingen::get('gesprekken', 'min_interval_actief_milisec');
		$actieve_deelnemers = 0;
		foreach ($this->getDeelnemers() as $andere_deelnemer) {
			if ($deelnemer->uid !== $andere_deelnemer->uid) {
				$diff = time() - strtotime($andere_deelnemer->gelezen_moment);
				if ($diff * 1000 < $max_interval) {
					$actieve_deelnemers++;
				}
			}
		}
		if ($actieve_deelnemers > 0) {
			$diff = time() - strtotime($deelnemer->gelezen_moment);
			$nieuw = $this->getAantalNieuweBerichten($deelnemer, $lastUpdate);
			if ($nieuw > 0) {
				$this->auto_update = 1000 * $diff / $nieuw;
			} else {
				$this->auto_update = 1000 * $diff / $actieve_deelnemers;
			}
			if ($this->auto_update < $min_interval) {
				$this->auto_update = $min_interval;
			}
		} else {
			$this->auto_update = false;
		}
		// Update deelnemer
		$deelnemer->gelezen_moment = getDateTime();
		GesprekDeelnemersModel::instance()->update($deelnemer);
		return GesprekBerichtenModel::instance()->getBerichtenSinds($this, $lastUpdate);
	}

	public function getAantalNieuweBerichten(GesprekDeelnemer $deelnemer, $lastUpdate) {
		if (!isset($this->aantal_nieuw)) {
			$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
			if ($lastUpdate < $toegevoegd) {
				$lastUpdate = $toegevoegd;
			}
			$this->aantal_nieuw = GesprekBerichtenModel::instance()->getAantalBerichtenSinds($this, $lastUpdate);
		}
		return $this->aantal_nieuw;
	}

}
