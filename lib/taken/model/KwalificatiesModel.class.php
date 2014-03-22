<?php

require_once 'MVC/model/entity/taken/CorveeKwalificatie.class.php';

/**
 * KwalificatiesModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class KwalificatiesModel extends PersistenceModel {

	public function __construct() {
		parent::__construct(new CorveeKwalificatie());
	}

	/**
	 * Lazy loading of corveefunctie.
	 * 
	 * @return CorveeKwalificatie[]
	 */
	public function getAlleKwalificaties() {
		return array_group_by('functie_id', $this->find());
	}

	/**
	 * Eager loading of corveefuncties.
	 * 
	 * @param string $lid_id
	 * @return CorveeFunctie[]
	 */
	public function getKwalificatiesVanLid($lid_id) {
		$model = new FunctiesModel();
		$functies = $model->getAlleFuncties(); // grouped by functie_id
		$kwalificaties = $this->find('lid_id = ?', array($lid_id));
		foreach ($kwalificaties as $kwali) {
			$kwali->setCorveeFunctie($functies[$kwali->getFunctieId()]);
		}
		return $kwalificaties;
	}

	public function isLidGekwalificeerdVoorFunctie($uid, $fid) {
		return $this->existsByPrimaryKey(array($uid, $fid));
	}

	public function newKwalificatie(CorveeFunctie $functie) {
		$kwalificatie = new CorveeKwalificatie();
		$kwalificatie->functie_id = $functie->functie_id;
		return $kwalificatie;
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