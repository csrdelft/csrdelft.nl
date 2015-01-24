<?php

/**
 * lidvoorkeur.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Voorkeur houdt voorkeuren bij van leden voor commissies
 */
class CommissieVoorkeurenModel {

	private $uid;

	public function __construct($uid) {
		$this->uid = $uid;
	}

	public function setCommissieVoorkeur($cid, $voorkeur) {
		$db = MijnSqli::instance();
		$query = 'DELETE FROM voorkeurVoorkeur WHERE uid =\'' . $this->uid . '\' AND cid = ' . $cid;
		$db->query($query);
		$query = 'INSERT INTO `voorkeurVoorkeur` VALUES ("'
				. $this->uid . '", '
				. $cid . ', 1, '
				. $voorkeur . ', CURRENT_TIMESTAMP )';
		$db->query($query);
	}

	public static function getCommissies() {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE zichtbaar = 1 ';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res[$row['id']] = $row['naam'];
		}
		return $res;
	}

	public function getVoorkeur() {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurVoorkeur WHERE actief = 1 AND uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res[$row['cid']] = $row['voorkeur'];
		}
		return $res;
	}

	public function getLidOpmerking() {
		$db = MijnSqli::instance();
		$query = 'SELECT lidOpmerking FROM voorkeurOpmerking WHERE uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = '';
		if (mysqli_num_rows($result) == 0)
			$this->insertRow();
		else
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$res = $row['lidOpmerking'];
			}
		return $res;
	}

	public function setLidOpmerking($opmerking) {
		$db = MijnSqli::instance();
		$query = 'UPDATE voorkeurOpmerking Set lidOpmerking = "' . $opmerking . '" WHERE uid = \'' . $this->uid . '\'';
		$db->query($query);
	}

	public function getPraesesOpmerking() {
		$db = MijnSqli::instance();
		$query = 'SELECT praesesOpmerking FROM voorkeurOpmerking WHERE uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = '';
		if (mysqli_num_rows($result) == 0)
			$this->insertRow();
		else
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$res = $row['praesesOpmerking'];
			}
		return $res;
	}

	public function setPraesesOpmerking($opmerking) {
		$db = MijnSqli::instance();
		$query = 'UPDATE voorkeurOpmerking Set praesesOpmerking = "' . $opmerking . '" WHERE uid = \'' . $this->uid . '\'';
		$db->query($query);
	}

	private function insertRow() {
		$db = MijnSqli::instance();
		$query = 'INSERT INTO voorkeurOpmerking VALUES ("' . $this->uid . '","","")';
		$db->query($query);
	}

}

class VoorkeurCommissie {

	private $cid;
	private $naam;

	public function __construct($cid, $naam) {
		$this->cid = $cid;
		$this->naam = $naam;
	}

	public function getGeinteresseerde() {
		$db = MijnSqli::instance();
		$query = 'SELECT uid, voorkeur FROM voorkeurCommissie JOIN voorkeurVoorkeur ON voorkeurCommissie.id = voorkeurVoorkeur.cid WHERE zichtbaar = 1 
			AND (voorkeur = 2 OR voorkeur = 3) AND cid = ' . $this->cid . ' ORDER BY voorkeur DESC';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$gedaan = false; //FIXME: GroepenOldModel::isUidLidofGroup($row['uid'], $this->naam, array('ht', 'ot'));
			$res[$row['uid']] = array('voorkeur' => $row['voorkeur'], 'gedaan' => $gedaan);
		}
		return $res;
	}

	public static function getCommissie($cid) {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE id = ' . $cid . '';
		$result = $db->select($query);
		$res = '';
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res = $row['naam'];
		}
		return new VoorkeurCommissie($cid, $res);
	}

	public static function getCommissies() {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE zichtbaar = 1 ';
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res[$row['id']] = $row['naam'];
		}
		return $res;
	}

	public function getNaam() {
		return $this->naam;
	}

}
