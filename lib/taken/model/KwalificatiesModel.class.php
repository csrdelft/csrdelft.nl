<?php

require_once 'MVC/model/entity/taken/CorveeKwalificatie.class.php';

/**
 * KwalificatiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class KwalificatiesModel extends PersistenceModel {

	public function __construct() {
		parent::__construct(new CorveeKwalificatie());
	}

	public function getAlleKwalificaties() {
		return array_group_by('functie_id', $this->find());
	}

	public function loadKwalificatiesVoorFunctie(CorveeFunctie $functie) {
		$functie->kwalificaties = $this->find('functie_id = ?', array($functie->functie_id));
	}

//TODO:
	public static function getKwalificatiesVanLid($uid) {
		$kwalificaties = self::loadKwalificaties('lid_id = ?', array($uid));
		$model = new FunctiesModel();
		$functies = $model->getAlleFuncties(true); // grouped by fid
		foreach ($kwalificaties as $kwali) {
			$kwali->setCorveeFunctie($functies[$kwali->getFunctieId()]);
		}
		return $kwalificaties;
	}

	public static function getIsLidGekwalificeerd($uid, $fid) {
		return self::existKwalificatie($uid, $fid);
	}

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 * 
	 * @param int $fid
	 * @return boolean
	 */
	public static function existFunctieKwalificaties($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Exist functie-kwalificaties faalt: Invalid $fid =' . $fid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_kwalificaties WHERE functie_id = ?)';
		$values = array($fid);
		$query = \Database::instance()->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	private static function existKwalificatie($uid, $fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Exist corvee-kwalificatie faalt: Invalid $fid =' . $fid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_kwalificaties WHERE lid_id = ? AND functie_id = ?)';
		$values = array($uid, $fid);
		$query = \Database::instance()->prepare($sql, $values);
		$query->execute($values);
		$result = (boolean) $query->fetchColumn();
		return $result;
	}

	private static function loadKwalificaties($where = null, $values = array(), $limit = null) {
		$sql = 'SELECT lid_id, functie_id, wanneer_toegewezen';
		$sql.= ' FROM crv_kwalificaties';
		if ($where !== null) {
			$sql.= ' WHERE ' . $where;
		}
		$sql.= ' ORDER BY lid_id ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\CorveeKwalificatie');
		return $result;
	}

	public static function kwalificatieToewijzen($fid, $uid) {
		if (self::existKwalificatie($uid, $fid)) {
			throw new Exception('Is al gekwalificeerd!');
		}
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT INTO crv_kwalificaties';
			$sql.= ' (lid_id, functie_id, wanneer_toegewezen)';
			$sql.= ' VALUES (?, ?, ?)';
			$wanneer = date('Y-m-d H:i');
			$values = array($uid, $fid, $wanneer);
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new Exception('New kwalificatie faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
			return new CorveeKwalificatie($uid, $fid, $wanneer);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function kwalificatieTerugtrekken($fid, $uid) {
		if (!self::existKwalificatie($uid, $fid)) {
			throw new Exception('Is niet gekwalificeerd!');
		}
		self::deleteKwalificatie($fid, $uid);
	}

	private static function deleteKwalificatie($fid, $uid = null) {
		$sql = 'DELETE FROM crv_kwalificaties';
		$sql.= ' WHERE functie_id = ?';
		$values = array($fid);
		if ($uid !== null) {
			$sql .= 'AND lid_id = ?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($uid !== null && $query->rowCount() !== 1) {
			throw new Exception('Delete kwalificatie faalt: $query->rowCount() =' . $query->rowCount());
		}
		return $query->rowCount();
	}

}

?>