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

	protected function __construct() {
		parent::__construct('groepen/');
	}

	public static function get($id) {
		return static::instance()->retrieveByPrimaryKey(array($id));
	}

}

class OnderverenigingenModel extends GroepenModel {

	const orm = 'Ondervereniging';

	protected static $instance;

}

class WoonoordenModel extends GroepenModel {

	const orm = 'Woonoord';

	protected static $instance;

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

}

class BesturenModel extends CommissiesModel {

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

class GroepLedenModel extends GroepenModel {

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

	public function getLedenVoorGroep(Groep $groep) {
		return $this->prefetch('groep_type = ? AND groep_id = ?', array(get_class($groep), $groep->id));
	}

	public function getStatistieken(Groep $groep) {
		$uids = array_keys(group_by_distinct('uid', $groep->getGroepLeden(), false));
		$count = count($uids);
		if ($count < 1) {
			return array();
		}
		$in = implode(', ', array_fill(0, $count, '?'));
		$stats['Totaal'] = $count;
		$stats['Verticale'] = Database::instance()->sqlSelect(array('verticale.naam', 'count(*)'), 'lid LEFT JOIN verticale ON(provielen.verticale = verticale.letter)', 'uid IN (' . $in . ')', $uids, 'verticale.naam', null)->fetchAll();
		$stats['Geslacht'] = Database::instance()->sqlSelect(array('geslacht', 'count(*)'), 'lid', 'uid IN (' . $in . ')', $uids, 'geslacht', null)->fetchAll();
		$stats['Lidjaar'] = Database::instance()->sqlSelect(array('lidjaar', 'count(*)'), 'lid', 'uid IN (' . $in . ')', $uids, 'lidjaar', null)->fetchAll();
		$stats['Opmerking'] = Database::instance()->sqlSelect(array('opmerking', 'count(*)'), GroepLid::getTableName(), 'groep_type = ? AND groep_id = ?', array(get_class($groep), $groep->id), 'opmerking', null)->fetchAll();
		return $stats;
	}

}
