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
	protected $default_order = 'laatste_update ASC';

	public static function get($gesprek_id) {
		return self::instance()->retrieveByPrimaryKey(array($gesprek_id));
	}

	public function nieuw($uid) {
		$gesprek = new Gesprek();
		$gesprek->laatste_uid = $uid;
		$gesprek->laatste_update = getDateTime();
		return $gesprek;
	}

	public function create(PersistentEntity $gesprek) {
		$gesprek->gesprek_id = (int) parent::create($gesprek);
	}

	public function delete(PersistentEntity $gesprek) {
		GesprekBerichtenModel::instance()->verwijderBerichtenVoorGesprek($gesprek);
		return parent::delete($gesprek);
	}

	public function startGesprek(Gesprek $gesprek, Account $from, Account $to) {
		$this->create($gesprek);
		GesprekDeelnemersModel::instance()->voegToeAanGesprek($to);
		return GesprekDeelnemersModel::instance()->voegToeAanGesprek($from);
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

	public function get($gesprek_id, $uid) {
		return self::instance()->retrieveByPrimaryKey(array($gesprek_id, $uid));
	}

	public function getDeelnemersVoorGesprek(Gesprek $gesprek) {
		return group_by_distinct('uid', $this->find('gesprek_id = ? ', array($gesprek->gesprek_id)));
	}

	public function getGesprekkenVoorLid($uid, $timestamp) {
		$gesprekken = array();
		foreach ($this->find('uid = ?', array($uid)) as $deelnemer) {
			$gesprek = GesprekkenModel::get($deelnemer->gesprek_id);
			if ($gesprek AND $gesprek->laatste_update > $timestamp) {
				$gesprekken[] = $gesprek;
			}
		}
		return $gesprekken;
	}

	public function getGesprekGelezen(Gesprek $gesprek, $uid) {
		$deelnemer = self::get($gesprek->gesprek_id, $uid);
		if (!$deelnemer) {
			throw new Exception('Geen deelnemer van gesprek');
		}
		return $deelnemer->gelezen_moment >= $gesprek->laatste_update;
	}

	public function sluitGesprek(GesprekDeelnemer $deelnemer) {
		if ($this->count('gesprek_id = ?', array($deelnemer->gesprek_id)) <= 1) {
			$gesprek = GesprekkenModel::get($deelnemer->gesprek_id);
			GesprekkenModel::instance()->verwijderGesprek($gesprek);
		}
	}

	public function voegToeAanGesprek(Gesprek $gesprek, Account $account) {
		$deelnemer = new GesprekDeelnemer();
		$deelnemer->gesprek_id = $gesprek->gesprek_id;
		$deelnemer->uid = $account->uid;
		$deelnemer->gelezen_moment = getDateTime(time() - 1);
		parent::create($deelnemer);
		return $deelnemer;
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
	protected $default_order = 'moment ASC';

	public static function get($bericht_id) {
		return $this->retrieveByPrimaryKey(array($bericht_id));
	}

	public function getBerichtenVoorGesprek(Gesprek $gesprek, $timestamp) {
		return $this->find('gesprek_id = ? AND moment > ?', array($gesprek->gesprek_id, getDateTime((int) $timestamp)));
	}

	public function maakBericht(Gesprek $gesprek, GesprekDeelnemer $deelnemer, $inhoud) {
		// Maak bericht
		$bericht = new GesprekBericht();
		$bericht->gesprek_id = $gesprek->gesprek_id;
		$bericht->moment = getDateTime();
		$bericht->auteur_uid = LoginModel::getUid();
		$bericht->inhoud = $inhoud;
		$bericht->id = $this->create($bericht);
		// Update gesprek
		$gesprek->laatste_update = $bericht->moment;
		$gesprek->laatste_uid = $bericht->door_uid;
		GesprekkenModel::instance()->update($gesprek);
		// Update deelnemer
		$deelnemer->gelezen_moment = $bericht->moment;
		GesprekDeelnemersModel::instance()->update($deelnemer);
		return $bericht;
	}

	public function verwijderBerichtenVoorGesprek(Gesprek $gesprek) {
		foreach ($this->find('gesprek_id = ?', array($gesprek->gesprek_id)) as $bericht) {
			$this->delete($bericht);
		}
	}

}
