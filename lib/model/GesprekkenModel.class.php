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

	public function getGesprekkenVoorLid($uid) {
		$gesprekken = array();
		foreach ($this->find('uid = ?', array($uid)) as $deelnemer) {
			$gesprekken[] = GesprekkenModel::get($deelnemer->gesprek_id);
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

	public function getBerichtenVoorGesprek(Gesprek $gesprek) {
		return $this->find('gesprek_id = ?', array($gesprek->gesprek_id));
	}

}
