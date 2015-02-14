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
		$diff = time() - strtotime($deelnemer->gelezen_moment);
		$aantal_nieuw = $this->getAantalNieuweBerichten($deelnemer, $lastUpdate);
		if ($aantal_nieuw > 0) {
			$this->auto_update = 1000 * $diff / $aantal_nieuw;
		} else {
			$total_delay = 0;
			$total_amount = 0;
			foreach (GesprekBerichtenModel::instance()->find('gesprek_id = ? AND auteur_uid != ?', array($deelnemer->gesprek_id, $deelnemer->uid), null, 'moment DESC', 5) as $bericht) {
				$total_amount++;
				$total_delay += time() - strtotime($bericht->moment);
			}
			if ($total_amount > 0) {
				$this->auto_update = 1000 * $total_delay / $total_amount;
			} else {
				$this->auto_update = 1000 * 10;
			}
		}
		if ($this->auto_update < 1000) {
			$this->auto_update = 1000;
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
