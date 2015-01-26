<?php

require_once 'model/entity/groepen/OpvolgbareGroep.abstract.php';
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

	public static function getUrl() {
		return '/groepen/' . get_called_class() . '/';
	}

	private static $old;

	/**
	 * Oude groep-id's omnummeren. 'snaam' mag ook.
	 * 
	 * @param int|string $id
	 * @return boolean
	 */
	public static function omnummeren($id) {
		if (!isset(self::$old)) {
			self::$old = DynamicEntityModel::makeModel('groep');
		}

		$groep = self::$old->find('id = ? OR (snaam = ? AND status = ?)', array($id, $id, 'ht'), null, null, 1)->fetch();
		if (!$groep) {
			setMelding('Groep niet gevonden: ' . htmlspecialchars($id), -1);
			return false;
		}

		$model = $groep->model;
		if (!class_exists($model)) {
			setMelding('Model niet gevonden: ' . $model, -1);
			return false;
		}

		return $model::get($groep->omnummering);
	}

	/**
	 * Groepen waarvan de gevraagde gebruiker de wikipagina's mag lezen en bewerken.
	 * 
	 * @param string $uid
	 * @return string
	 */
	public static function getWikiToegang($uid) {
		$result = array();
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			return $result;
		}
		// S_CIE's die als normaal lid mogen inloggen op de wiki
		$hardcodedToegang = array('x271', 'x030'); // oudledenbestuur & stichting CC

		if ($profiel->isLid() OR $profiel->isOudlid() OR in_array($profiel->uid, $hardcodedToegang)) {
			$result[] = 'htleden-oudleden';
		}

		foreach (CommissieLedenModel::instance()->find('uid = ?') as $lid) {
			$commissie = CommissiesModel::get($lid->groep_id);
			if ($commissie->status === GroepStatus::HT) {
				$result[] = $commissie->opvolg_naam;
			}
		}
		return $result;
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
		$groep->maker_uid = LoginModel::getUid();
		$groep->rechten_beheren = null;
		return $groep;
	}

	public function create(PersistentEntity $groep) {
		$groep->id = (int) parent::create($groep);
	}

	/**
	 * Converteer groep inclusief leden van klasse.
	 * 
	 * @param Groep $converteer
	 * @return boolean
	 */
	public function converteer(Groep $converteer) {
		// groep converteren
		try {
			$groep = $this->nieuw();
			cast($groep, $converteer);
			$groep->id = null;

			setMelding(get_class($groep) . '   ' . $groep->getTableName(), 2);

			$this->create($groep);
		} catch (Exception $e) {
			setMelding('Converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// leden converteren
		try {
			$leden = $groep::leden;
			$model = $leden::instance();
			foreach ($converteer->getLeden() as $lid) {
				$groeplid = $model->nieuw($groep, $lid->uid);
				cast($groeplid, $lid);
				$groeplid->groep_id = $groep->id;
				$model->create($groeplid);
			}
		} catch (Exception $e) {
			setMelding('Leden converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		return $groep;
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

abstract class OpvolgbareGroepenModel extends GroepenModel {

	const orm = 'OpvolgbareGroep';

	protected static $instance;

	public static function get($id) {
		if (is_int($id)) {
			return parent::get($id);
		}
		return static::instance()->find('opvolg_naam = ? AND status = ?', array($id, GroepStatus::HT), null, null, 1)->fetch();
	}

	public function nieuw() {
		$groep = parent::nieuw();
		$groep->opvolg_naam = null;
		$groep->jaargang = LichtingenModel::getHuidigeJaargang();
		$groep->status = GroepStatus::HT;
		return $groep;
	}

}

class KringenModel extends OpvolgbareGroepenModel {

	const orm = 'Kring';

	protected static $instance;

	public function nieuw() {
		$kring = parent::nieuw();
		$kring->verticale = null;
		return $kring;
	}

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

	public function nieuw() {
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = null;
		return $bestuur;
	}

}

class ActiviteitenModel extends OpvolgbareGroepenModel {

	const orm = 'Activiteit';

	protected static $instance;

	public function nieuw() {
		$activiteit = parent::nieuw();
		$activiteit->soort = ActiviteitSoort::Intern;
		$activiteit->locatie = null;
		$activiteit->rechten_aanmelden = null;
		$activiteit->aanmeld_limiet = null;
		$activiteit->aanmelden_vanaf = getDateTime();
		$activiteit->aanmelden_tot = getDateTime();
		$activiteit->kosten_bedrag = null;
		$activiteit->machtiging_rekening = null;
		return $activiteit;
	}

}

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

	protected static $instance;

	public function nieuw() {
		$ketzer = parent::nieuw();
		$ketzer->rechten_aanmelden = null;
		$ketzer->aanmeld_limiet = null;
		$ketzer->aanmelden_vanaf = getDateTime();
		$ketzer->aanmelden_tot = getDateTime();
		$ketzer->kosten_bedrag = null;
		$ketzer->machtiging_rekening = null;
		return $ketzer;
	}

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
