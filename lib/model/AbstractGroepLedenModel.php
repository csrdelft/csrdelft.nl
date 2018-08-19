<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;

/**
 * AbstractGroepLedenModel.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
abstract class AbstractGroepLedenModel extends CachedPersistenceModel {
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

	/**
	 * @param AbstractGroep $groep
	 * @param $uid
	 *
	 * @return AbstractGroepLid|false
	 */
	public static function get(AbstractGroep $groep, $uid) {
		return static::instance()->retrieveByPrimaryKey(array($groep->id, $uid));
	}

	/**
	 * @param AbstractGroep $groep
	 * @param $uid
	 *
	 * @return AbstractGroepLid
	 */
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
		$sqlIn = implode(', ', array_fill(0, $count, '?'));
		$stats['Verticale'] = Database::instance()->sqlSelect(array('naam', 'count(*)'), 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $sqlIn . ')', $uids, 'verticale', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), ProfielModel::instance()->getTableName(), 'uid IN (' . $sqlIn . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lichting'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), ProfielModel::instance()->getTableName(), 'uid IN (' . $sqlIn . ')', $uids, 'lidjaar', null)->fetchAll();
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
