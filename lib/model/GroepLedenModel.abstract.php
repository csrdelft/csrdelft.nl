<?php

/**
 * GroepLedenModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class AbstractGroepLedenModel extends CachedPersistenceModel {

	const DIR = 'groepen/';

	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'lid_sinds ASC';
	/**
	 * Store leden array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

	public static function get(AbstractGroep $groep, $uid) {
		return static::instance()->retrieveByPrimaryKey(array($groep->id, $uid));
	}

	public function nieuw(AbstractGroep $groep, $uid) {
		$orm = static::ORM;
		$lid = new $orm();
		$lid->groep_id = $groep->id;
		$lid->uid = $uid;
		$lid->door_uid = LoginModel::getUid();
		$lid->lid_sinds = getDateTime();
		$lid->opmerking = null;
		return $lid;
	}

	/**
	 * Return leden van groep.
	 * 
	 * @param AbstractGroep $groep
	 * @return AbstractGroepLid[]
	 */
	public function getLedenVoorGroep(AbstractGroep $groep) {
		return $this->prefetch('groep_id = ?', array($groep->id));
	}

	/**
	 * Bereken statistieken van de groepleden.
	 * 
	 * @param AbstractGroep $groep
	 * @return array
	 */
	public function getStatistieken(AbstractGroep $groep) {
		$leden = group_by_distinct('uid', $groep->getLeden());
		if (empty($leden)) {
			return array();
		}
		$uids = array_keys($leden);
		$count = count($uids);
		$in = implode(', ', array_fill(0, $count, '?'));
		$stats['Verticale'] = Database::instance()->sqlSelect(array('naam', 'count(*)'), 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $in . ')', $uids, 'verticale', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), ProfielModel::instance()->getTableName(), 'uid IN (' . $in . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lichting'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), ProfielModel::instance()->getTableName(), 'uid IN (' . $in . ')', $uids, 'lidjaar', null)->fetchAll();
		$stats['Tijd'] = array();
		foreach ($leden as $groeplid) {
			$time = strtotime($groeplid->lid_sinds) * 1000;
			if (isset($stats['Tijd'][$time])) {
				$stats['Tijd'][$time] += 1;
			} else {
				$stats['Tijd'][$time] = 1;
			}
		}
		$stats['Totaal'] = $count;
		if (property_exists($groep, 'aanmeld_limiet')) {
			if ($groep->aanmeld_limiet === null) {
				$stats['Totaal'] .= ' (geen limiet)';
			} else {
				$stats['Totaal'] .= ' van ' . $groep->aanmeld_limiet;
			}
		}
		return $stats;
	}

}

class RechtenGroepLedenModel extends AbstractGroepLedenModel {

	const ORM = 'RechtenGroepLid';

	protected static $instance;

}

class OnderverenigingsLedenModel extends AbstractGroepLedenModel {

	const ORM = 'OnderverenigingsLid';

	protected static $instance;

}

class BewonersModel extends AbstractGroepLedenModel {

	const ORM = 'Bewoner';

	protected static $instance;

}

class LichtingLedenModel extends AbstractGroepLedenModel {

	const ORM = 'LichtingsLid';

	protected static $instance;

	/**
	 * Create LichtingLid on the fly.
	 * 
	 * @param Lichting $lichting
	 * @param string $uid
	 * @return LichtingLid|false
	 */
	public static function get(AbstractGroep $lichting, $uid) {
		$profiel = ProfielModel::get($uid);
		if ($profiel AND $profiel->lidjaar === $lichting->lidjaar) {
			$lid = static::instance()->nieuw($lichting, $uid);
			$lid->door_uid = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}

	/**
	 * Return leden van lichting.
	 * 
	 * @param Lichting $lichting
	 * @return LichtingLid[]
	 */
	public function getLedenVoorGroep(AbstractGroep $lichting) {
		$leden = array();
		foreach (ProfielModel::instance()->prefetch('lidjaar = ?', array($lichting->lidjaar)) as $profiel) {
			$lid = static::get($lichting, $profiel->uid);
			if ($lid) {
				$leden[] = $lid;
			}
		}
		return $leden;
	}

}

class VerticaleLedenModel extends AbstractGroepLedenModel {

	const ORM = 'VerticaleLid';

	protected static $instance;

	/**
	 * Create VerticaleLid on the fly.
	 * 
	 * @param Verticale $verticale
	 * @param string $uid
	 * @return VerticaleLid|false
	 */
	public static function get(AbstractGroep $verticale, $uid) {
		$profiel = ProfielModel::get($uid);
		if ($profiel AND $profiel->verticale === $verticale->letter) {
			$lid = static::instance()->nieuw($verticale, $uid);
			if ($profiel->verticaleleider) {
				$lid->opmerking = 'Leider';
			} elseif ($profiel->kringcoach) {
                		$lid->opmerking = 'Kringcoach';
            		}
			$lid->door_uid = null;
			$lid->lid_sinds = $profiel->lidjaar . '-09-01 00:00:00';
			return $lid;
		}
		return false;
	}

	/**
	 * Return leden van verticale.
	 * 
	 * @param Verticale $verticale
	 * @return VerticaleLid[]
	 */
	public function getLedenVoorGroep(AbstractGroep $verticale) {
		require_once 'model/entity/LidStatus.enum.php';
		$leden = array();
		$status = LidStatus::$lidlike;
		$where = 'verticale = ? AND status IN (' . implode(', ', array_fill(0, count($status), '?')) . ')';
		array_unshift($status, $verticale->letter);
		foreach (ProfielModel::instance()->prefetch($where, $status) as $profiel) {
			$lid = static::get($verticale, $profiel->uid);
			if ($lid) {
				$leden[] = $lid;
			}
		}
		return $leden;
	}

}

class KringLedenModel extends AbstractGroepLedenModel {

	const ORM = 'KringLid';

	protected static $instance;

}

class CommissieLedenModel extends AbstractGroepLedenModel {

	const ORM = 'CommissieLid';

	protected static $instance;

}

class BestuursLedenModel extends AbstractGroepLedenModel {

	const ORM = 'BestuursLid';

	protected static $instance;

}

class KetzerDeelnemersModel extends AbstractGroepLedenModel {

	const ORM = 'KetzerDeelnemer';

	protected static $instance;

}

class WerkgroepDeelnemersModel extends KetzerDeelnemersModel {

	const ORM = 'WerkgroepDeelnemer';

	protected static $instance;

}

class ActiviteitDeelnemersModel extends KetzerDeelnemersModel {

	const ORM = 'ActiviteitDeelnemer';

	protected static $instance;

}
