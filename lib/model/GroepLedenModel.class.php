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
	protected $default_order = 'volgorde ASC, lid_sinds ASC';
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
		$lid->lid_tot = null;
		$lid->opmerking = null;
		$lid->status = GroepStatus::HT;
		$lid->volgorde = 0;
		return $lid;
	}

	/**
	 * Get leden by uid.
	 * 
	 * @param Groep $groep
	 * @param GroepStatus $status
	 * @return array
	 */
	public function getLedenVoorGroep(Groep $groep, $status = null) {
		$where = 'groep_id = ?';
		$params = array($groep->id);
		if ($status !== null AND in_array($status, GroepStatus::getTypeOptions())) {
			$where .= ' AND status = ?';
			$params[] = $status;
		}
		return group_by_distinct('uid', $this->prefetch($where, $params));
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
		$stats['Totaal'] = $count;
		if (property_exists($groep, 'aanmeld_limiet')) {
			$stats['Totaal'] .= ' van ' . $groep->aanmeld_limiet;
		}
		$stats['Verticale'] = Database::instance()->sqlSelect(array('naam', 'count(*)'), 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $in . ')', $uids, 'verticale', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lidjaar'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'lidjaar', null)->fetchAll();
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
