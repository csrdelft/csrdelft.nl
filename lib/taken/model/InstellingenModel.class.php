<?php
namespace Taken\MLT;

require_once 'taken/model/entity/Instelling.class.php';

/**
 * InstellingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class InstellingenModel {

	private static $_instellingen = null;
	private static $_defaults = array(
		'standaard_maaltijdprijs' => '3',
		'marge_gasten_verhouding' => '10',
		'marge_gasten_min' => '3',
		'marge_gasten_max' => '4',
		'corveepunten_per_jaar' => '10'
	);
	
	public static function loadAlleInstellingen() {
		$instellingen = self::getAlleInstellingen();
		foreach ($instellingen as $instelling) {
			$GLOBALS[$instelling->getInstellingId()] = $instelling->getWaarde();
		}
		foreach (self::$_defaults as $key => $value) {
			if (!array_key_exists($key, $GLOBALS)) {
				self::$_instellingen[] = self::newInstelling($key, $value);
				$GLOBALS[$key] = $value;
			}
		}
	}
	
	public static function getAlleInstellingen() {
		if (self::$_instellingen === null) {
			self::$_instellingen = self::loadInstellingen();
		}
		return self::$_instellingen;
	}
	
	public static function getInstelling($key) {
		$instellingen = self::$_instellingen;
		if ($instellingen === null) {
			$instellingen = self::loadInstellingen('instelling_id = ?', array($key), 1);
		}
		if (!array_key_exists(0, $instellingen)) {
			throw new \Exception('Get instelling faalt: Not found $key ='. $key);
		}
		return $instellingen[0];
	}
	
	private static function loadInstellingen($where=null, $values=array(), $limit=null) {
		$sql = 'SELECT instelling_id, waarde';
		$sql.= ' FROM mlt_instellingen';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' ORDER BY instelling_id ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Taken\MLT\Instelling');
		return $result;
	}
	
	public static function saveInstelling($key, $value) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$instelling = self::getInstelling($key);
			if ($instelling === null) {
				$instelling = self::newInstelling($key, $value);
			}
			else {
				$instelling->setWaarde($value);
				self::updateInstelling($instelling);
			}
			$db->commit();
			return $instelling;
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	private static function newInstelling($key, $value) {
		$sql = 'INSERT INTO mlt_instellingen';
		$sql.= ' (instelling_id, waarde)';
		$sql.= ' VALUES (?, ?)';
		$values = array($key, $value);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New instelling faalt: $query->rowCount() ='. $query->rowCount());
		}
		return new Instelling($key, $value);
	}
	
	private static function updateInstelling(Instelling $instelling) {
		$sql = 'UPDATE mlt_instellingen';
		$sql.= ' SET waarde = ?';
		$sql.= ' WHERE instelling_id = ?';
		$values = array(
			$instelling->getWaarde(),
			$instelling->getInstellingId()
		);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Update instelling faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	public static function verwijderInstelling($key) {
		self::deleteInstelling($key);
	}
	
	private static function deleteInstelling($key) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$sql = 'DELETE FROM mlt_instellingen';
			$sql.= ' WHERE instelling_id = ?';
			$values = array($key);
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new \Exception('Delete instelling faalt: $query->rowCount() ='. $query->rowCount());
			}
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
}