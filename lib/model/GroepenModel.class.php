<?php

require_once 'model/entity/groepen/OpvolgbareGroep.class.php';

/**
 * GroepenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenModel extends CachedPersistenceModel {

	const orm = 'Groep';

	protected static $instance;

	public static function get($id) {
		return static::instance()->retrieveByPrimaryKey(array($id));
	}

	protected function __construct() {
		parent::__construct('groepen/');
	}

	public function nieuw() {
		$class = static::orm;
		$groep = new $class();
		$groep->naam = null;
		$groep->samenvatting = null;
		$groep->omschrijving = null;
		$groep->begin_moment = getDateTime();
		$groep->eind_moment = null;
		$groep->website = null;
		$groep->door_uid = LoginModel::getUid();
		return $groep;
	}
	
	public function create(PersistentEntity $entity) {
		$entity->item_id = (int) parent::create($entity);
		$this->flushCache(true);
	}

}

class OnderverenigingenModel extends GroepenModel {

	const orm = 'Ondervereniging';

	protected static $instance;

	public function nieuw() {
		$ondervereniging = parent::nieuw();
		$ondervereniging->status = OnderverenigingStatus::AdspirantOndervereniging;
		$ondervereniging->status_historie = '[div]Aangemaakt als ' . OnderverenigingStatus::getDescription($ondervereniging->status) . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $ondervereniging;
	}

}

class WoonoordenModel extends GroepenModel {

	const orm = 'Woonoord';

	protected static $instance;

	public function nieuw() {
		$woonoord = parent::nieuw();
		$woonoord->status = HuisStatus::Woonoord;
		$woonoord->status_historie = '[div]Aangemaakt als ' . HuisStatus::getDescription($woonoord->status) . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $woonoord;
	}

}

/**
 * TODO: extend GroepenModel
 */
class LichtingenModel {

	public static function getHuidigeJaargang() {
		$jaargang = self::getJongsteLichting();
		return $jaargang . '-' . ($jaargang + 1);
	}

	public static function getJongsteLichting() {
		return (int) Database::sqlSelect(array('MAX(lidjaar)'), 'profielen')->fetchColumn();
	}

	public static function getOudsteLichting() {
		return (int) Database::sqlSelect(array('MIN(lidjaar)'), 'profielen', 'lidjaar > 0')->fetchColumn();
	}

}

class VerticalenModel extends GroepenModel {

	const orm = 'Verticale';

	protected static $instance;
	/**
	 * Store verticalen array as a whole in memcache
	 * @var boolean
	 */
	protected $memcache_prefetch = true;

}

class OpvolgbareGroepenModel extends GroepenModel {

	const orm = 'OpvolgbareGroep';

	protected static $instance;

	public function nieuw() {
		$groep = parent::nieuw();
		$groep->familie_id = null;
		$groep->jaargang = LichtingenModel::getHuidigeJaargang();
		$groep->status = GroepStatus::HT;
		return $groep;
	}

}

class KringenModel extends OpvolgbareGroepenModel {

	const orm = 'Kring';

	protected static $instance;

}

class WerkgroepenModel extends OpvolgbareGroepenModel {

	const orm = 'Werkgroep';

	protected static $instance;

}

class CommissiesModel extends OpvolgbareGroepenModel {

	const orm = 'Commissie';

	protected static $instance;

	public function nieuw() {
		$commissie = parent::nieuw();
		$commissie->soort = CommissieSoort::Commissie;
		return $commissie;
	}

}

class BesturenModel extends OpvolgbareGroepenModel {

	const orm = 'Bestuur';

	protected static $instance;

}

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

	protected static $instance;

}

class ActiviteitenModel extends KetzersModel {

	const orm = 'Activiteit';

	protected static $instance;

}

class ConferentiesModel extends ActiviteitenModel {

	const orm = 'Conferentie';

	protected static $instance;

}

class KetzerSelectorsModel extends GroepenModel {

	const orm = 'KetzerSelector';

	protected static $instance;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->find('ketzer_id = ?', array($ketzer->id));
	}

}

class KetzerOptiesModel extends GroepenModel {

	const orm = 'KetzerOptie';

	protected static $instance;

	public function getOptiesVoorSelect(KetzerSelector $select) {
		return $this->find('select_id = ?', array($select->select_id));
	}

}

class KetzerKeuzesModel extends GroepenModel {

	const orm = 'KetzerKeuze';

	protected static $instance;

	public function getKeuzesVoorOptie(KetzerOptie $optie) {
		return $this->find('optie_id = ?', array($optie->optie_id));
	}

}

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

	protected function __construct() {
		parent::__construct('groepen/');
	}

	public function nieuw(Groep $groep, $uid) {
		$lid = new GroepLid();
		$lid->groep_class = get_class($groep);
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

	public function getLedenVoorGroep(Groep $groep, $status = null) {
		$where = 'groep_class = ? AND groep_id = ?';
		$params = array(get_class($groep), $groep->id);
		if (in_array($status, GroepStatus::getTypeOptions())) {
			$where .= ' AND status = ?';
			$params[] = $status;
		}
		return $this->prefetch($where, $params);
	}

	public function getStatistieken(Groep $groep) {
		$uids = array_keys(group_by_distinct('uid', $groep->getLeden(), false));
		$count = count($uids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		$stats['Totaal'] = $count;
		$stats['Verticale'] = Database::instance()->sqlSelect(array('verticale.naam', 'count(*)'), 'profielen LEFT JOIN verticale ON(provielen.verticale = verticale.letter)', 'uid IN (' . $in . ')', $uids, 'verticale.naam', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lidjaar'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), 'profielen', 'uid IN (' . $in . ')', $uids, 'lidjaar', null)->fetchAll();
		return $stats;
	}

}
