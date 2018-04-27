<?php

namespace CsrDelft\model\gesprekken;

use CsrDelft\model\entity\gesprekken\Gesprek;
use CsrDelft\model\entity\gesprekken\GesprekDeelnemer;
use CsrDelft\model\entity\security\Account;
use CsrDelft\model\InstellingenModel;
use CsrDelft\Orm\PersistenceModel;

/**
 * GesprekDeelnemersModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class GesprekDeelnemersModel extends PersistenceModel {

	const ORM = GesprekDeelnemer::class;

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'toegevoegd_moment ASC';

	/**
	 * @param int $gesprek_id
	 * @param string $uid
	 *
	 * @return GesprekDeelnemer|false
	 */
	public static function get($gesprek_id, $uid) {
		return static::instance()->retrieveByPrimaryKey(array($gesprek_id, $uid));
	}

	/**
	 * @param Gesprek $gesprek
	 *
	 * @return GesprekDeelnemer[]|\PDOStatement
	 */
	public function getDeelnemersVanGesprek(Gesprek $gesprek) {
		return $this->find('gesprek_id = ? ', array($gesprek->gesprek_id));
	}

	/**
	 * @param string $uid
	 * @param int $lastUpdate
	 *
	 * @return Gesprek[]
	 */
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

	/**
	 * @param string $uid
	 *
	 * @return int
	 */
	public function getAantalNieuweBerichtenVoorLid($uid) {
		$totaal = 0;
		foreach ($this->getGesprekkenVoorLid($uid, 0) as $gesprek) {
			$totaal += $gesprek->aantal_nieuw;
		}
		return $totaal;
	}

	/**
	 * @param Gesprek $gesprek
	 * @param Account $account
	 * @param GesprekDeelnemer|null $door
	 *
	 * @return bool|GesprekDeelnemer
	 */
	public function voegToeAanGesprek(Gesprek $gesprek, Account $account, GesprekDeelnemer $door = null) {
		if (count($gesprek->getDeelnemers()) >= (int)InstellingenModel::get('gesprekken', 'max_aantal_deelnemers')) {
			return false;
		}
		$deelnemer = new GesprekDeelnemer();
		$deelnemer->gesprek_id = $gesprek->gesprek_id;
		$deelnemer->uid = $account->uid;
		$deelnemer->toegevoegd_moment = getDateTime(time() - 1);
		$deelnemer->gelezen_moment = getDateTime(0);
		parent::create($deelnemer);
		if ($door) {
			$inhoud = 'Ik heb ' . $account->getProfiel()->getLink() . ' toegevoegd aan het gesprek.';
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $door, $inhoud);
		}
		return $deelnemer;
	}

	/**
	 * @param Gesprek $gesprek
	 * @param GesprekDeelnemer $deelnemer
	 *
	 * @return bool
	 */
	public function verlaatGesprek(Gesprek $gesprek, GesprekDeelnemer $deelnemer) {
		$rowCount = $this->delete($deelnemer);
		if ($this->count('gesprek_id = ?', array($gesprek->gesprek_id)) === 0) {
			GesprekkenModel::instance()->verwijderGesprek($gesprek);
		} else {
			$inhoud = 'Ik heb het gesprek verlaten.';
			GesprekBerichtenModel::instance()->maakBericht($gesprek, $deelnemer, $inhoud);
		}
		return $rowCount === 1;
	}

}
