<?php


use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/maalcie/CorveeVrijstelling.class.php';

/**
 * CorveeVrijstellingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeVrijstellingenModel extends PersistenceModel {
	const ORM = CorveeVrijstelling::class;
	const DIR = 'maalcie/';

	protected static $instance;

	public function nieuw($uid = null, $begin = null, $eind = null, $percentage = 0) {
		$vrijstelling = new CorveeVrijstelling();
		$vrijstelling->uid = $uid;
		if ($begin === null) {
			$begin = date('Y-m-d');
		}
		$vrijstelling->begin_datum = $begin;
		if ($eind === null) {
			$eind = date('Y-m-d');
		}
		$vrijstelling->eind_datum = $eind;
		if ($percentage === null) {
			$percentage = intval(Instellingen::get('corvee', 'standaard_vrijstelling_percentage'));
		}
		$vrijstelling->percentage = $percentage;

		return $vrijstelling;
	}

	public function getAlleVrijstellingen($groupByUid=false) {
		$vrijstellingen = $this->find();
		if ($groupByUid) {
			$vrijstellingenByUid = array();
			foreach ($vrijstellingen as $vrijstelling) {
				$vrijstellingenByUid[$vrijstelling->uid] = $vrijstelling;
			}
			return $vrijstellingenByUid;
		}
		return $vrijstellingen;
	}
	
	public function getVrijstelling($uid) {
		return $this->retrieveByPrimaryKey(array($uid));
	}
	
	public function saveVrijstelling($uid, $begin, $eind, $percentage) {
		$db = Database::instance();
		try {
			$db->beginTransaction();
			$vrijstelling = $this->getVrijstelling($uid);
			if ($vrijstelling === false) {
				$vrijstelling = $this->nieuw($uid, $begin, $eind, $percentage);
				$this->create($vrijstelling);
			}
			else {
				$vrijstelling->begin_datum = $begin;
				$vrijstelling->eind_datum = $eind;
				$vrijstelling->percentage = $percentage;
				$this->update($vrijstelling);
			}
			$db->commit();
			return $vrijstelling;
		}
		catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}
	
	public function verwijderVrijstelling($uid) {
		$this->deleteByPrimaryKey(array($uid));
	}
}

?>