<?php
/*
 * C.S.R. Delft pubcie@csrdelft.nl
 *
 * Voorkeur houdt voorkeuren bij van leden voor commissies
 */


//require_once 'instellingen.class.php';


class Commissie {
	
	private $cid;
	private $naam;
	
	public function __construct($cid, $naam) {
		$this->cid = $cid;
		$this->naam = $naam;
	}
	
	public function getGeinteresseerde() {
		$db = MySql::instance();
		$query = 'SELECT uid, voorkeur FROM Commissie JOIN Voorkeur ON Commissie.id = Voorkeur.cid WHERE zichtbaar = 1 
			AND (voorkeur = 2 OR voorkeur = 3) AND cid = '.$this->cid.' ORDER BY voorkeur DESC';
		$result = $db->select($query);
		$res = array();
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res[$row['uid']] = $row['voorkeur'];
		}
		return $res;
	}
	
	public static function getCommissie($cid) {
			$db = MySql::instance();
			$query = 'SELECT * FROM Commissie WHERE id = ' . $cid .'';
			$result = $db->select($query);
			$res = '';
			while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$res = $row['naam'];
			}
			return new Commissie($cid, $res);
	}
	
	public static function getCommissies() {
		$db = MySql::instance();
		$query = 'SELECT * FROM Commissie WHERE zichtbaar = 1 ';
		$result = $db->select($query);
		$res = array();
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res[$row['id']] = $row['naam'];
		}
		return $res;
	}
	
	public function getNaam() {
		return $this->naam;
	}

}
?>
