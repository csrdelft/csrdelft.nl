<?php
/*
 * C.S.R. Delft pubcie@csrdelft.nl
 *
 * Voorkeur houdt voorkeuren bij van leden voor commissies
 */


//require_once 'instellingen.class.php';


class DiesAanmelding {
	
	private $uid;
	
	public function __construct($uid) {
		$this->uid=$uid;
	}
	
	public function filledInBefore() {
		$db = MySql::instance();
		$query = 'SELECT COUNT(*) FROM diesaanmelding WHERE uid = '. $this->uid;
		$result = $db->select($query);
		$res = '';
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res = $row['COUNT(*)'];
		}
		return $res==1;
	}
	
	public function getData() {
		$db = MySql::instance();
		$query = 'SELECT * FROM diesaanmelding WHERE uid = '. $this->uid;
		$result = $db->select($query);
		$res=array();
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$res=$row;
		}
		return $res;
	}
	
	
	
	public function setData($naamDate, $eetZelf, $eetDate, $allerZelf, $allerDate, $date18) {
		$db = MySql::instance();
		$query = 'DELETE FROM diesaanmelding WHERE uid =' . $this->uid;
		$db->query($query);
		$query = 'INSERT INTO `diesaanmelding` VALUES ("' 
			. $this->uid .'","'
			. $naamDate .'",'
			. $eetZelf .','
			. $eetDate .',"'
			. $allerZelf .'","'
			. $allerDate .'","'
			. $date18 .'")';
		$db->query($query);
	}
	
}
?>
