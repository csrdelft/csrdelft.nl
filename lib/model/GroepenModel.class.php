<?php

require_once 'model/entity/groepen/Groep.class.php';
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
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'begin_moment DESC';

	public static function get($id) {
		if (is_numeric($id)) {
			return static::instance()->retrieveByPrimaryKey(array($id));
		}
		return static::instance()->find('familie = ? AND status = ?', array($id, GroepStatus::HT), null, null, 1)->fetch();
	}

	public static function getNaam() {
		return strtolower(str_replace('Model', '', get_called_class()));
	}

	public static function getUrl() {
		return '/groepen/' . static::getNaam() . '/';
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
		if ($profiel->isLid() OR $profiel->isOudlid()) {
			$result[] = 'htleden-oudleden';
		}
		// 1 generatie vooruit en 1 achteruit (default order by)
		$ft = BesturenModel::instance()->find('status = ?', array(GroepStatus::FT), null, null, 1)->fetch();
		$ht = BesturenModel::instance()->find('status = ?', array(GroepStatus::HT), null, null, 1)->fetch();
		$ot = BesturenModel::instance()->find('status = ?', array(GroepStatus::OT), null, null, 1)->fetch();
		if (($ft AND $ft->getLid($uid)) OR ( $ht AND $ht->getLid($uid)) OR ( $ot AND $ot->getLid($uid))) {
			$result[] = 'bestuur';
		}
		foreach (CommissieLedenModel::instance()->find('uid = ?', array($uid)) as $commissielid) {
			$commissie = CommissiesModel::get($commissielid->groep_id);
			if ($commissie->status === GroepStatus::HT OR $commissie->status === GroepStatus::FT) {
				$result[] = $commissie->familie;
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
		$groep->naam = '';
		$groep->familie = null;
		$groep->status = GroepStatus::HT;
		$groep->samenvatting = '';
		$groep->omschrijving = null;
		$groep->begin_moment = getDateTime();
		$groep->eind_moment = null;
		$groep->website = null;
		$groep->maker_uid = LoginModel::getUid();
		return $groep;
	}

	/**
	 * Set primary key.
	 * 
	 * @param PersistentEntity $groep
	 */
	public function create(PersistentEntity $groep) {
		$groep->id = (int) parent::create($groep);
	}

	/**
	 * Delete ACL.
	 * 
	 * @param array $primary_key_values
	 * @return int rows affected
	 */
	protected function deleteByPrimaryKey(array $primary_key_values) {
		AccessModel::instance()->setAcl(static::orm, reset($primary_key_values), array());
		return parent::deleteByPrimaryKey($primary_key_values);
	}

	/**
	 * Converteer groep inclusief leden van klasse.
	 * 
	 * @param Groep $oldgroep
	 * @param GroepenModel $oldmodel
	 * @param string $soort
	 * @return boolean
	 */
	public function converteer(Groep $oldgroep, GroepenModel $oldmodel, $soort = null) {
		// groep converteren
		try {
			$newgroep = $this->nieuw($soort);
			foreach ($oldgroep->getValues() as $attr => $value) {
				if (property_exists($newgroep, $attr)) {
					$newgroep->$attr = $value;
				}
			}
			$newgroep->id = null;
			$this->create($newgroep);
		} catch (Exception $e) {
			setMelding('Converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// leden converteren
		try {
			$leden = $newgroep::leden;
			$ledenmodel = $leden::instance();
			foreach ($oldgroep->getLeden() as $oldlid) {
				$newlid = $ledenmodel->nieuw($newgroep, $oldlid->uid);
				foreach ($oldlid->getValues() as $attr => $value) {
					if (property_exists($newlid, $attr)) {
						$newlid->$attr = $value;
					}
				}
				$newlid->groep_id = $newgroep->id;
				$ledenmodel->create($newlid);
			}
		} catch (Exception $e) {
			setMelding('Leden converteren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// omnummeren
		try {
			if (!isset(self::$old)) {
				self::$old = DynamicEntityModel::makeModel('groep');
			}
			$omnummering = self::$old->find('omnummering = ? AND model = ?', array($oldgroep->id, get_class($oldmodel)), null, null, 1)->fetch();
			if ($omnummering) {
				$omnummering->omnummering = $newgroep->id;
				$omnummering->model = get_class($this);
				self::$old->update($omnummering);
			}
		} catch (Exception $ex) {
			setMelding('Omnummeren mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// leden verwijderen
		try {
			$oldleden = $oldgroep::leden;
			$oldledenmodel = $oldleden::instance();
			foreach ($oldgroep->getLeden() as $oldlid) {
				$oldledenmodel->delete($oldlid);
			}
		} catch (Exception $ex) {
			setMelding('Leden verwijderen mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		// groep verwijderen
		try {
			$oldmodel->delete($oldgroep);
		} catch (Exception $ex) {
			setMelding('Groep verwijderen mislukt: ' . $e->getMessage(), -1);
			return false;
		}
		return $newgroep;
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
	/**
	 * Default ORDER BY
	 * @var string
	 */
	protected $default_order = 'letter ASC';

	public static function get($letter) {
		return static::instance()->retrieveByPrimaryKey(array($letter));
	}

}

class KringenModel extends GroepenModel {

	const orm = 'Kring';

	protected static $instance;

	public function nieuw() {
		$kring = parent::nieuw();
		$kring->verticale = '';
		return $kring;
	}

}

class CommissiesModel extends GroepenModel {

	const orm = 'Commissie';

	protected static $instance;

	public function nieuw($soort = null) {
		if (!in_array($soort, CommissieSoort::getTypeOptions())) {
			$soort = CommissieSoort::Commissie;
		}
		$commissie = parent::nieuw();
		$commissie->soort = $soort;
		return $commissie;
	}

}

class BesturenModel extends GroepenModel {

	const orm = 'Bestuur';

	protected static $instance;

	public function nieuw() {
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}

}

class KetzersModel extends GroepenModel {

	const orm = 'Ketzer';

	protected static $instance;

	public function nieuw() {
		$ketzer = parent::nieuw();
		$ketzer->aanmeld_limiet = null;
		$ketzer->aanmelden_vanaf = getDateTime();
		$ketzer->aanmelden_tot = $ketzer->aanmelden_vanaf;
		$ketzer->bewerken_tot = $ketzer->aanmelden_tot;
		$ketzer->afmelden_tot = null;
		return $ketzer;
	}

}

class WerkgroepenModel extends KetzersModel {

	const orm = 'Werkgroep';

	protected static $instance;

}

class ActiviteitenModel extends KetzersModel {

	const orm = 'Activiteit';

	protected static $instance;

	public function nieuw($soort = null) {
		if (!in_array($soort, ActiviteitSoort::getTypeOptions())) {
			$soort = ActiviteitSoort::Verticale;
		}
		$activiteit = parent::nieuw();
		$activiteit->soort = $soort;
		$activiteit->locatie = null;
		$activiteit->in_agenda = true;
		return $activiteit;
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
