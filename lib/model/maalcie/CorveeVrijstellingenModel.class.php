<?php


require_once 'model/entity/maalcie/CorveeVrijstelling.class.php';

/**
 * CorveeVrijstellingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeVrijstellingenModel {

	public static function getAlleVrijstellingen($groupByUid=false) {
		$vrijstellingen = self::loadVrijstellingen();
		if ($groupByUid) {
			$vrijstellingenByUid = array();
			foreach ($vrijstellingen as $vrijstelling) {
				$vrijstellingenByUid[$vrijstelling->getUid()] = $vrijstelling;
			}
			return $vrijstellingenByUid;
		}
		return $vrijstellingen;
	}
	
	public static function getVrijstelling($uid) {
		$vrijstellingen = self::loadVrijstellingen('uid = ?', array($uid), 1);
		if (!array_key_exists(0, $vrijstellingen)) {
			return null; //throw new Exception('Get vrijstelling faalt: Not found $uid ='. $uid);
		}
		return $vrijstellingen[0];
	}

	/**
	 * @param null $where
	 * @param array $values
	 * @param null $limit
	 * @return CorveeVrijstelling[]
	 */
	private static function loadVrijstellingen($where=null, $values=array(), $limit=null) {
		$sql = 'SELECT uid, begin_datum, eind_datum, percentage';
		$sql.= ' FROM crv_vrijstellingen';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' ORDER BY begin_datum ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\CorveeVrijstelling');
		return $result;
	}
	
	public static function saveVrijstelling($uid, $begin, $eind, $percentage) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$vrijstelling = self::getVrijstelling($uid);
			if ($vrijstelling === null) {
				$vrijstelling = self::newVrijstelling($uid, $begin, $eind, $percentage);
			}
			else {
				$vrijstelling->setBeginDatum($begin);
				$vrijstelling->setEindDatum($eind);
				$vrijstelling->setPercentage($percentage);
				self::updateVrijstelling($vrijstelling);
			}
			$db->commit();
			return $vrijstelling;
		}
		catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}
	
	private static function newVrijstelling($uid, $begin, $eind, $percentage) {
		$sql = 'INSERT INTO crv_vrijstellingen';
		$sql.= ' (uid, begin_datum, eind_datum, percentage)';
		$sql.= ' VALUES (?, ?, ?, ?)';
		$values = array($uid, $begin, $eind, $percentage);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New vrijstelling faalt: $query->rowCount() ='. $query->rowCount());
		}
		return new CorveeVrijstelling($uid, $begin, $eind, $percentage);
	}
	
	private static function updateVrijstelling(CorveeVrijstelling $vrijstelling) {
		$sql = 'UPDATE crv_vrijstellingen';
		$sql.= ' SET begin_datum=?, eind_datum=?, percentage=?';
		$sql.= ' WHERE uid=?';
		$values = array(
			$vrijstelling->getBeginDatum(),
			$vrijstelling->getEindDatum(),
			$vrijstelling->getPercentage(),
			$vrijstelling->getUid()
		);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('Update vrijstelling faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	public static function verwijderVrijstelling($uid) {
		self::deleteVrijstelling($uid);
	}
	
	private static function deleteVrijstelling($uid) {
		$sql = 'DELETE FROM crv_vrijstellingen';
		$sql.= ' WHERE uid = ?';
		$values = array($uid);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('Delete vrijstelling faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
}

?>