<?php

/**
 * GesprekkenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekkenModel extends PersistenceModel {

	const orm = 'Gesprek';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'laatste_update DESC';

	protected function __construct() {
		parent::__construct('gesprekken/');
	}

	public static function get($gesprek_id) {
		return self::instance()->retrieveByPrimaryKey(array($gesprek_id));
	}

	public function startGesprek(Account $from, Account $to, $inhoud) {
		// Maak gesprek
		$gesprek = new Gesprek();
		$gesprek->laatste_update = getDateTime();
		$gesprek->laatste_bericht = '';
		$gesprek->gesprek_id = (int) $this->create($gesprek);
		// Deelnemers toevoegen
		$deelnemer = GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $from);
		GesprekDeelnemersModel::instance()->voegToeAanGesprek($gesprek, $to);
		// Maak bericht
		GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $inhoud);
		return $gesprek;
	}

	public function verwijderGesprek(Gesprek $gesprek) {
		GesprekBerichtenModel::instance()->verwijderBerichtenVoorGesprek($gesprek);
		return $this->delete($gesprek);
	}

}

/**
 * GesprekDeelnemersModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekDeelnemersModel extends PersistenceModel {

	const orm = 'GesprekDeelnemer';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'toegevoegd_moment ASC';

	protected function __construct() {
		parent::__construct('gesprekken/');
	}

	public static function get($gesprek_id, $uid) {
		return self::instance()->retrieveByPrimaryKey(array($gesprek_id, $uid));
	}

	public function getDeelnemersVanGesprek(Gesprek $gesprek) {
		return $this->find('gesprek_id = ? ', array($gesprek->gesprek_id));
	}

	public function getAantalDeelnemersVanGesprek(Gesprek $gesprek) {
		return $this->count('gesprek_id = ?', array($gesprek->gesprek_id));
	}

	public function getGesprekkenVoorLid($uid, $lastUpdate) {
		$gesprekken = array();
		foreach ($this->find('uid = ?', array($uid)) as $deelnemer) {
			$gesprek = GesprekkenModel::get($deelnemer->gesprek_id);
			if ($gesprek AND $gesprek->laatste_update > $lastUpdate) {
				$gesprekken[] = $gesprek;
			}
			$gesprek->getAantalNieuweBerichten($deelnemer, strtotime($deelnemer->gelezen_moment));
		}
		return $gesprekken;
	}

	public function getAantalNieuweBerichtenVoorLid($uid) {
		$totaal = 0;
		foreach ($this->getGesprekkenVoorLid($uid, 0) as $gesprek) {
			$totaal += $gesprek->aantal_nieuw;
		}
		return $totaal;
	}

	public function voegToeAanGesprek(Gesprek $gesprek, Account $account, Account $door = null) {
		if (count($gesprek->getDeelnemers()) >= (int) Instellingen::get('gesprekken', 'max_aantal_deelnemers')) {
			return false;
		}
		$deelnemer = new GesprekDeelnemer();
		$deelnemer->gesprek_id = $gesprek->gesprek_id;
		$deelnemer->uid = $account->uid;
		$deelnemer->toegevoegd_moment = getDateTime(time() - 1);
		$deelnemer->gelezen_moment = getDateTime(0);
		parent::create($deelnemer);
		if ($door) {
			$inhoud = 'Ik heb ' . $account->getProfiel()->getNaam() . ' toegevoegd aan het gesprek.';
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $door, $inhoud);
		}
		return $deelnemer;
	}

	public function verlaatGesprek(Gesprek $gesprek, GesprekDeelnemer $deelnemer) {
		$rowCount = $this->delete($deelnemer);
		if ($this->count('gesprek_id = ?', array($gesprek->gesprek_id)) === 0) {
			GesprekkenModel::instance()->verwijderGesprek($gesprek);
		}
		return $rowCount === 1;
	}

}

/**
 * GesprekBerichtenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekBerichtenModel extends PersistenceModel {

	const orm = 'GesprekBericht';

	protected static $instance;
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'bericht_id ASC';

	protected function __construct() {
		parent::__construct('gesprekken/');
	}

	public static function get($bericht_id) {
		return $this->retrieveByPrimaryKey(array($bericht_id));
	}

	public function getBerichtenSinds(Gesprek $gesprek, $lastUpdate) {
		return $this->find('gesprek_id = ? AND moment > ?', array($gesprek->gesprek_id, getDateTime($lastUpdate)));
	}

	public function getAantalBerichtenSinds(Gesprek $gesprek, $lastUpdate) {
		return $this->count('gesprek_id = ? AND moment > ?', array($gesprek->gesprek_id, getDateTime($lastUpdate)));
	}

	public function maakBericht(Gesprek $gesprek, GesprekDeelnemer $deelnemer, $inhoud) {
		// Maak bericht
		$bericht = new GesprekBericht();
		$bericht->gesprek_id = $gesprek->gesprek_id;
		$bericht->moment = getDateTime();
		$bericht->auteur_uid = $deelnemer->uid;
		$bericht->inhoud = $inhoud;
		$bericht->id = $this->create($bericht);
		// Update gesprek
		$gesprek->laatste_update = $bericht->moment;
		$gesprek->laatste_bericht = $bericht->getAuteurFormatted() . CsrBB::parse(mb_substr($bericht->inhoud, 0, 30));
		if (mb_strlen($bericht->inhoud) > 30) {
			$gesprek->laatste_bericht .= '...';
		}
		GesprekkenModel::instance()->update($gesprek);
		return $bericht;
	}

	public function verwijderBerichtenVoorGesprek(Gesprek $gesprek) {
		foreach ($this->find('gesprek_id = ?', array($gesprek->gesprek_id)) as $bericht) {
			$this->delete($bericht);
		}
	}

}
