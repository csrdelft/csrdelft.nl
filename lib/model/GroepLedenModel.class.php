<?php

/**
 * GroepLedenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepLedenModel extends CachedPersistenceModel {

	const orm = 'GroepLid';

	protected static $instance;
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

	public static function get(Groep $groep, $uid) {
		return static::instance()->retrieveByPrimaryKey(array($groep->id, $uid));
	}

	protected function __construct() {
		parent::__construct('groepen/');
	}

	public function nieuw(Groep $groep, $uid) {
		$class = static::orm;
		$lid = new $class();
		$lid->groep_id = $groep->id;
		$lid->uid = $uid;
		$lid->door_uid = LoginModel::getUid();
		$lid->lid_sinds = getDateTime();
		$lid->opmerking = null;
		return $lid;
	}

	/**
	 * Return leden van groep by uid.
	 * 
	 * @param Groep $groep
	 * @return GroepLid[]
	 */
	public function getLedenVoorGroep(Groep $groep) {
		return group_by_distinct('uid', $this->prefetch('groep_id = ?', array($groep->id)));
	}

	/**
	 * Bereken statistieken van de groepleden.
	 * 
	 * @param Groep $groep
	 * @return array
	 */
	public function getStatistieken(Groep $groep) {
		$uids = array_keys($groep->getLeden());
		$count = count($uids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		$stats['Verticale'] = Database::instance()->sqlSelect(array('naam', 'count(*)'), 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $in . ')', $uids, 'verticale', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lichting'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'lidjaar', null)->fetchAll();
		$stats['Tijd'] = array();
		foreach ($groep->getLeden() as $groeplid) {
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

class OnderverLedenModel extends GroepLedenModel {

	const orm = 'OnderverLid';

	protected static $instance;

}

class BewonersModel extends GroepLedenModel {

	const orm = 'Bewoner';

	protected static $instance;

}

class LichtingLedenModel extends GroepLedenModel {

	const orm = 'LichtingsLid';

	protected static $instance;

}

class VerticaleLedenModel extends GroepLedenModel {

	const orm = 'VerticaleLid';

	protected static $instance;

}

class KringLedenModel extends GroepLedenModel {

	const orm = 'KringLid';

	protected static $instance;

}

class WerkgroepDeelnemersModel extends GroepLedenModel {

	const orm = 'WerkgroepDeelnemer';

	protected static $instance;

}

class CommissieLedenModel extends GroepLedenModel {

	const orm = 'CommissieLid';

	protected static $instance;

}

class BestuursLedenModel extends GroepLedenModel {

	const orm = 'BestuursLid';

	protected static $instance;

}

class ActiviteitDeelnemersModel extends GroepLedenModel {

	const orm = 'ActiviteitDeelnemer';

	protected static $instance;

}

class KetzerDeelnemersModel extends GroepLedenModel {

	const orm = 'KetzerDeelnemer';

	protected static $instance;

}
