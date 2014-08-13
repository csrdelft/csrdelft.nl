<?php

class DiesAanmelding {

	private $uid;

	public function __construct($uid) {
		$this->uid = $uid;
	}

	public function filledInBefore() {
		$db = MijnSqli::instance();
		$query = 'SELECT COUNT(*) FROM diesaanmelding WHERE uid = ' . $this->uid;
		$result = $db->select($query);
		$res = '';
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res = $row['COUNT(*)'];
		}
		return $res == 1;
	}

	public function getData() {
		$db = MijnSqli::instance();
		$query = 'SELECT * FROM diesaanmelding WHERE uid = ' . $this->uid;
		$result = $db->select($query);
		$res = array();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res = $row;
		}
		return $res;
	}

	public function galaVol() {
		$db = MijnSqli::instance();
		$query = 'SELECT COUNT(*) FROM diesaanmelding';
		$result = $db->select($query);
		$res = '';
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$res = $row['COUNT(*)'];
		}
		return $res >= 100;
	}

	public function setData($naamDate, $eetZelf, $eetDate, $allerZelf, $allerDate, $date18) {
		$db = MijnSqli::instance();
		$query = 'DELETE FROM diesaanmelding WHERE uid =' . $this->uid;
		$db->query($query);
		$query = 'INSERT INTO `diesaanmelding` VALUES ("'
				. $this->uid . '","'
				. $naamDate . '",'
				. $eetZelf . ','
				. $eetDate . ',"'
				. $allerZelf . '","'
				. $allerDate . '","'
				. $date18 . '")';
		$db->query($query);
	}

}
