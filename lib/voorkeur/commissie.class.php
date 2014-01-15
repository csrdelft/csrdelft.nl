<?php

class Commissie {

	private $cid;
	private $naam;

	public function __construct($cid, $naam) {
		$this->cid = $cid;
		$this->naam = $naam;
	}

	public function getGeinteresseerde() {
		$db = MySql::instance();
		$query = 'SELECT uid, voorkeur FROM voorkeurCommissie JOIN voorkeurVoorkeur ON voorkeurCommissie.id = voorkeurVoorkeur.cid WHERE zichtbaar = 1 
			AND (voorkeur = 2 OR voorkeur = 3) AND cid = ' . $this->cid . ' ORDER BY voorkeur DESC';
		$result = $db->select($query);
		$res = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$gedaan = Groepen::isUidLidofGroup($row['uid'], $this->naam, array('ht', 'ot'));
			$res[$row['uid']] = array('voorkeur' => $row['voorkeur'], 'gedaan' => $gedaan);
		}
		return $res;
	}

	public static function getCommissie($cid) {
		$db = MySql::instance();
		$query = 'SELECT * FROM voorkeurCommissie WHERE id = ' . $cid . '';
		$result = $db->select($query);
		$res = '';
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res = $row['naam'];
		}
		return new Commissie($cid, $res);
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

	public function getNaam() {
		return $this->naam;
	}

}
