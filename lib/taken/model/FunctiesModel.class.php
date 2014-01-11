<?php


require_once 'taken/model/entity/CorveeFunctie.class.php';

/**
 * FunctiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class FunctiesModel {

	public static function getAlleFuncties($groupByFid=false) {
		$functies = self::loadFuncties(null, array(), null, $groupByFid);
		if ($groupByFid) {
			$functiesByFid = array();
			foreach ($functies as $functie) {
				$functiesByFid[$functie->getFunctieId()] = $functie;
			}
			return $functiesByFid;
		}
		return $functies;
	}
	
	public static function getFunctie($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new \Exception('Get functie faalt: Invalid $fid ='. $fid);
		}
		$functies = self::loadFuncties('functie_id = ?', array($fid), 1);
		if (!array_key_exists(0, $functies)) {
			throw new \Exception('Get functie faalt: Not found $fid ='. $fid);
		}
		return $functies[0];
	}
	
	private static function loadFuncties($where=null, $values=array(), $limit=null) {
		$sql = 'SELECT functie_id, naam, afkorting, email_bericht, standaard_punten, kwalificatie_benodigd';
		$sql.= ' FROM crv_functies';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' ORDER BY naam ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\CorveeFunctie');
		return $result;
	}
	
	public static function saveFunctie($fid, $naam, $afk, $email, $punten, $kwali) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			if ($fid === 0) {
				$functie = self::newFunctie($naam, $afk, $email, $punten, $kwali);
			}
			else {
				$functie = self::getFunctie($fid);
				$functie->setNaam($naam);
				$functie->setAfkorting($afk);
				$functie->setEmailBericht($email);
				$functie->setStandaardPunten($punten);
				$functie->setKwalificatieBenodigd($kwali);
				self::updateFunctie($functie);
				if (!$kwali) { // niet (meer) voorkeurbaar
					KwalificatiesModel::verwijderKwalificaties($fid);
				}
			}
			$db->commit();
			return $functie;
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	private static function newFunctie($naam, $afk, $email, $punten, $kwali) {
		$sql = 'INSERT INTO crv_functies';
		$sql.= ' (functie_id, naam, afkorting, email_bericht, standaard_punten, kwalificatie_benodigd)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?)';
		$values = array(null, $naam, $afk, $email, $punten, $kwali);
		$db = \Database::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New functie faalt: $query->rowCount() ='. $query->rowCount());
		}
		return new CorveeFunctie(intval($db->lastInsertId()), $naam, $afk, $email, $punten, $kwali);
	}
	
	private static function updateFunctie(CorveeFunctie $functie) {
		$sql = 'UPDATE crv_functies';
		$sql.= ' SET naam=?, afkorting=?, email_bericht=?, standaard_punten=?, kwalificatie_benodigd=?';
		$sql.= ' WHERE functie_id=?';
		$values = array(
			$functie->getNaam(),
			$functie->getAfkorting(),
			$functie->getEmailBericht(),
			$functie->getStandaardPunten(),
			$functie->getIsKwalificatieBenodigd(),
			$functie->getFunctieId()
		);
		$db = \Database::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Update functie faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	public static function verwijderFunctie($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new \Exception('Verwijder functie faalt: Invalid $fid ='. $fid);
		}
		if (TakenModel::existFunctieTaken($fid)) {
			throw new \Exception('Verwijder eerst de bijbehorende corveetaken!');
		}
		if (CorveeRepetitiesModel::existFunctieRepetities($fid)) {
			throw new \Exception('Verwijder eerst de bijbehorende corveerepetities!');
		}
		self::deleteFunctie($fid);
	}
	
	private static function deleteFunctie($fid) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			KwalificatiesModel::verwijderKwalificaties($fid); // delete kwalificaties first (foreign key)
			$sql = 'DELETE FROM crv_functies';
			$sql.= ' WHERE functie_id = ?';
			$values = array($fid);
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new \Exception('Delete functie faalt: $query->rowCount() ='. $query->rowCount());
			}
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
}

?>