<?php

/**
 * lidvoorkeur.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 * Voorkeur houdt voorkeuren bij van leden voor commissies
 */
class Lidvoorkeur {

	private $uid;

	public function __construct($uid) {
		$this->uid = $uid;
	}

	public function setCommissieVoorkeur($cid, $voorkeur) {
		$db = MySql::instance();
		$query = 'DELETE FROM voorkeurVoorkeur WHERE uid =\'' . $this->uid . '\' AND cid = ' . $cid;
		$db->query($query);
		$query = 'INSERT INTO `voorkeurVoorkeur` VALUES ("'
				. $this->uid . '", '
				. $cid . ', 1, '
				. $voorkeur . ', CURRENT_TIMESTAMP )';
		$db->query($query);
	}

	public static function getCommissies() {
		$db = MySql::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE zichtbaar = 1 ';
		$result = $db->select($query);
		$res = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res[$row['id']] = $row['naam'];
		}
		return $res;
	}

	public function getVoorkeur() {
		$db = MySql::instance();
		$query = 'SELECT * FROM voorkeurVoorkeur WHERE actief = 1 AND uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res[$row['cid']] = $row['voorkeur'];
		}
		return $res;
	}

	public function getLidOpmerking() {
		$db = MySql::instance();
		$query = 'SELECT lidOpmerking FROM voorkeurOpmerking WHERE uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = '';
		if (mysql_num_rows($result) == 0)
			$this->insertRow();
		else
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$res = $row['lidOpmerking'];
			}
		return $res;
	}

	public function setLidOpmerking($opmerking) {
		$db = MySql::instance();
		$query = 'UPDATE voorkeurOpmerking Set lidOpmerking = "' . $opmerking . '" WHERE uid = \'' . $this->uid . '\'';
		$db->query($query);
	}

	public function getPraesesOpmerking() {
		$db = MySql::instance();
		$query = 'SELECT praesesOpmerking FROM voorkeurOpmerking WHERE uid =\'' . $this->uid . '\'';
		$result = $db->select($query);
		$res = '';
		if (mysql_num_rows($result) == 0)
			$this->insertRow();
		else
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$res = $row['praesesOpmerking'];
			}
		return $res;
	}

	public function setPraesesOpmerking($opmerking) {
		$db = MySql::instance();
		$query = 'UPDATE voorkeurOpmerking Set praesesOpmerking = "' . $opmerking . '" WHERE uid = \'' . $this->uid . '\'';
		$db->query($query);
	}

	private function insertRow() {
		$db = MySql::instance();
		$query = 'INSERT INTO voorkeurOpmerking VALUES ("' . $this->uid . '","","")';
		$db->query($query);
	}

}
