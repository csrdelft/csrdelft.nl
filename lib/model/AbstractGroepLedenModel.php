<?php

namespace CsrDelft\model;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\model\entity\interfaces\HeeftAanmeldLimiet;
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
		return static::instance()->retrieveByPrimaryKey([$groep->id, $uid]);
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
		return $this->prefetch('groep_id = ?', [$groep->id]);
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
			return [];
		}
		$uids = array_keys($leden);
		$count = count($uids);
		$sqlIn = implode(', ', array_fill(0, $count, '?'));
		$tijd = [];
		foreach ($leden as $groeplid) {
			$time = strtotime($groeplid->lid_sinds);
			if (isset($tijd[$time])) {
				$tijd[$time] += 1;
			} else {
				$tijd[$time] = 1;
			}
		}
		$totaal = $count;
		if ($groep instanceof HeeftAanmeldLimiet) {
			if ($groep->getAanmeldLimiet() === null) {
				$totaal .= ' (geen limiet)';
			} else {
				$totaal .= ' van ' . $groep->getAanmeldLimiet();
			}
		}
		return [
			'totaal' => $totaal,
			'verticale' => Database::instance()->sqlSelect(['naam', 'count(*)'], 'profielen LEFT JOIN verticalen ON profielen.verticale = verticalen.letter', 'uid IN (' . $sqlIn . ')', $uids, 'verticale', null)->fetchAll(),
			'geslacht' => Database::instance()->sqlSelect(['geslacht', 'count(*)'], ProfielModel::instance()->getTableName(), 'uid IN (' . $sqlIn . ')', $uids, 'geslacht', null)->fetchAll(),
			'lichting' => Database::instance()->sqlSelect(['lidjaar', 'count(*)'], ProfielModel::instance()->getTableName(), 'uid IN (' . $sqlIn . ')', $uids, 'lidjaar', null)->fetchAll(),
			'tijd' => $tijd,
		];
	}

}
