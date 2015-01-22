<?php

require_once 'model/entity/groepen/OpvolgbareGroep.class.php';
require_once 'model/GroepLedenModel.class.php';

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

	public function create(PersistentEntity $groep) {
		$groep->id = (int) parent::create($groep);
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
