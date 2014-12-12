<?php

/**
 * verticale.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 */
class OldVerticale {

	public $nummer;
	public $naam;
	public $kringen = array();

	public function __construct($nummer, $kringen = array()) {
		if (preg_match('/^[A-Z]{1}$/', $nummer)) {
			$nummer = array_search($nummer, OldVerticale::getLetters());
		}

		if (!array_key_exists($nummer, OldVerticale::getNamen())) {
			throw new Exception('Verticale bestaat niet');
		}
		$this->nummer = $nummer;
		$this->naam = OldVerticale::getNaamById($nummer);
	}

	public function loadKringen() {
		$db = MijnSqli::instance();
		$query = "
			SELECT kring, GROUP_CONCAT(uid ORDER BY kringleider DESC, achternaam ASC) as kringleden
			FROM lid
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL' OR (status='S_OUDLID' AND kring>0))
			  AND verticale=" . $this->nummer . "
			GROUP BY kring
			ORDER BY kring";
		$result = $db->query($query);
		while ($row = $db->next($result)) {
			$this->addKring($row['kring'], $row['kringleden']);
		}
	}

	public function getNaam() {
		return $this->naam;
	}

	public function getLetter() {
		return self::getLetterById($this->nummer);
	}

	public function getKringen() {
		return $this->kringen;
	}

	public function getKring($kring) {
		if (sizeof($this->kringen) == 0) {
			$this->loadKringen();
		}
		if (!array_key_exists($kring, $this->kringen)) {
			throw new Exception('Kring bestaat niet');
		}
		return $this->kringen[$kring];
	}

	public function addKring($kring, $kringleden) {
		$leden = explode(',', $kringleden);
		$this->kringen[$kring] = array();
		foreach ($leden as $uid) {
			$this->kringen[$kring][] = LidCache::getLid($uid);
		}
	}

	public static function getNaamById($nummer) {
		$namen = self::getNamen();
		return $namen[$nummer];
	}

	public static function getLetterById($nummer) {
		$letters = self::getLetters();
		return $letters[$nummer];
	}

	public static function getNamen() {
		$db = MijnSqli::instance();
		$query = "
			SELECT naam
			FROM verticalen";
		$result = $db->query($query);
		while ($row = $db->next($result)) {
			$namen[] = $row['naam'];
		}
		return $namen;
	}

	public static function getLetters() {
		$db = MijnSqli::instance();
		$letters = array();
		$query = "
			SELECT letter
			FROM verticalen";
		$result = $db->query($query);
		while ($row = $db->next($result)) {
			$letters[] = $row['letter'];
		}
		return $letters;
	}

	public static function getAll() {
		$db = MijnSqli::instance();
		$query = "
			SELECT verticale, kring, GROUP_CONCAT(uid ORDER BY kringleider DESC, achternaam ASC) as kringleden
			FROM lid
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL' OR (status='S_OUDLID' AND kring>0))
			  AND verticale !=0
			GROUP BY verticale, kring
			ORDER BY verticale, kring";
		$result = $db->query($query);

		$vID = 0;
		$verticalen = array();

		while ($row = $db->next($result)) {
			if ($vID != $row['verticale']) { // nieuwe verticale
				if (isset($verticale)) { // save vorige
					$verticalen[] = $verticale;
				}
				$vID = $row['verticale'];
				$verticale = new OldVerticale($vID);
			}
			$verticale->addKring($row['kring'], $row['kringleden']);
		}
		$verticalen[] = $verticale;

		return $verticalen;
	}

}
