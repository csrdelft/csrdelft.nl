<?php
namespace CsrDelft\model;
use function CsrDelft\getDateTime;
use function CsrDelft\group_by_distinct;
use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\model\entity\groepen\ActiviteitDeelnemer;
use CsrDelft\model\entity\groepen\BestuursLid;
use CsrDelft\model\entity\groepen\Bewoner;
use CsrDelft\model\entity\groepen\CommissieLid;
use CsrDelft\model\entity\groepen\KetzerDeelnemer;
use CsrDelft\model\entity\groepen\KringLid;
use CsrDelft\model\entity\groepen\Lichting;
use CsrDelft\model\entity\groepen\LichtingsLid;
use CsrDelft\model\entity\groepen\OnderverenigingsLid;
use CsrDelft\model\entity\groepen\RechtenGroepLid;
use CsrDelft\model\entity\groepen\Verticale;
use CsrDelft\model\entity\groepen\VerticaleLid;
use CsrDelft\model\entity\groepen\WerkgroepDeelnemer;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\CachedPersistenceModel;
use CsrDelft\Orm\Persistence\Database;

/**
 * AbstractGroepLedenModel.php
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

	const ORM = RechtenGroepLid::class;

	protected static $instance;

}

class KetzerDeelnemersModel extends AbstractGroepLedenModel {

	const ORM = KetzerDeelnemer::class;

	protected static $instance;

}

class WerkgroepDeelnemersModel extends KetzerDeelnemersModel {

	const ORM = WerkgroepDeelnemer::class;

	protected static $instance;

}

class ActiviteitDeelnemersModel extends KetzerDeelnemersModel {

	const ORM = ActiviteitDeelnemer::class;

	protected static $instance;

}
