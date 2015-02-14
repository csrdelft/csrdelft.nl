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
		$toegevoegd = strtotime($deelnemer->toegevoegd_moment);
		if ($timestamp < $toegevoegd) {
			$timestamp = $toegevoegd;
		}
		$gelezen = strtotime($deelnemer->gelezen_moment);
		if ($timestamp > $gelezen) {
			$deelnemer->gelezen_moment = getDateTime();
			GesprekDeelnemersModel::instance()->update($deelnemer);
		}
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
