<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\common\MijnSqli;
use CsrDelft\model\security\LoginModel;

/**
 * BiebCatalogus.class.php  |  Gerrit Uitslag
 *
 * catalogus
 *
 */
class BiebCatalogus {

	private $aBoeken = array();
	private $iTotaal;
	private $iGefilterdTotaal;
	private $aKolommen = array(); // kolommen van de tabel. De laatste velden die niet in tabel staan worden gebruik om op te filteren.
	private $iKolommenZichtbaar; //aantal kolommen zichtbaar in de tabel.

	public function __construct() {
		$this->loadCatalogusdata();
	}

	/**
	 * Zet json in elkaar voor dataTables om catalogustabel mee te vullen
	 * Filters en sortering worden aan de hand van parameters uit _GET ingesteld
	 *
	 * @return json
	 */
	protected function loadCatalogusdata() {
		/*
		 * Script:    DataTables server-side script for PHP and MySQL
		 * Copyright: 2010 - Allan Jardine
		 * License:   GPL v2 or BSD (3-point)
		 */


		// kolommen van de tabel. De laatste velden die niet in tabel staan worden gebruik om op te filteren.
		if (LoginModel::mag('P_BIEB_READ')) {
			//boekstatus
			$this->aKolommen = array('titel', 'auteur', 'categorie', 'bsaantal', 'eigenaar', 'lener', 'uitleendatum', 'status', 'code', 'isbn', 'auteur', 'categorie');
			$this->iKolommenZichtbaar = 7;
		} else {
			//catalogus
			$this->aKolommen = array('titel', 'auteur', 'categorie', 'code', 'isbn');
			$this->iKolommenZichtbaar = 3;
		}

		/* MySQL */
		$db = MijnSqli::instance();

		/*
		 * Paging
		 */
		$sLimit = "";
		if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
			$sLimit = "LIMIT " . (int)$_GET['iDisplayStart'] . ", " . (int)$_GET['iDisplayLength'];
		}


		/*
		 * Ordering
		 */

		//sorteerkeys voor mysql
		$aSortColumns = array('titel' => "titel", 'auteur' => "auteur", 'categorie' => "categorie",
			'code' => "code", 'isbn' => "isbn", 'bsaantal' => "bsaantal",
			'status' => "status", 'leningen' => "leningen", 'eigenaar' => "eigenaar",
			'lener' => "lener", 'uitleendatum' => "uitleendatum");

		//is er een kolom gegeven om op te sorteren?
		if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY ";
			//loop als er op meer kolommen tegelijk gesorteerd moet worden
			for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
				//mag kolom gesorteerd worden?
				if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
					$sOrder .= $aSortColumns[$this->aKolommen[intval($_GET['iSortCol_' . $i])]] . " " . $db->escape($_GET['sSortDir_' . $i]) . ", ";
				}
			}
			$sOrder = substr_replace($sOrder, "", -2);

			if ($sOrder == "ORDER BY ") {
				$sOrder = "";
			}
		}


		/**
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		//sorteer key voor mysql
		$aFilterColumns = array('titel' => "titel", 'auteur' => "auteur", 'categorie' => "CONCAT(c1.categorie, ' - ', c2.categorie )",
			'code' => "code", 'isbn' => "isbn", 'bsaantal' => "bsaantal", 'status' => "e.status", 'leningen' => "leningen",
			'eigenaar' => "CONCAT(l1.voornaam, ' ', l1.tussenvoegsel,IFNULL(l1.tussenvoegsel, ' '),l1.achternaam)",
			'lener' => "CONCAT(l2.voornaam, ' ',l2.tussenvoegsel, IFNULL(l1.tussenvoegsel, ' '),l2.achternaam)",
			'uitleendatum' => "uitleendatum");

		$sWhere = "";
		if ($_GET['sSearch'] != "") {
			$sWhere = "WHERE (";
			for ($i = 0; $i < count($this->aKolommen); $i++) {
				//beschrijvingenaantal skippen, die heeft een subquery nodig
				if ($this->aKolommen[$i] != 'bsaantal') {
					$sWhere .= $aFilterColumns[$this->aKolommen[$i]] . " LIKE '%" . $db->escape($_GET['sSearch']) . "%' OR ";
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
		}

		/*
		 * filter op eigenaar
		 */

		//filter bepalen
		$allow = array('alle', 'csr', 'leden', 'eigen', 'geleend');
		if (LoginModel::mag('P_BIEB_READ') AND in_array($_GET['sEigenaarFilter'], $allow)) {
			$filter = $_GET['sEigenaarFilter'];
		} else {
			$filter = 'csr';
		}

		if ($sWhere == "") {
			$sBeginWh = "WHERE ";
		} else {
			$sBeginWh = " AND ";
		}
		switch ($filter) {
			case 'csr':
				$sWhere .= $sBeginWh . "e.eigenaar_uid='x222'";
				break;
			case 'leden':
				$sWhere .= $sBeginWh . "e.eigenaar_uid NOT LIKE 'x222'";
				break;
			case 'eigen':
				$sWhere .= $sBeginWh . "e.eigenaar_uid='" . $db->escape(LoginModel::getUid()) . "'";
				break;
			case 'geleend':
				$sWhere .= $sBeginWh . "(e.status = 'uitgeleend' OR e.status = 'teruggegeven')AND e.uitgeleend_uid='" . $db->escape(LoginModel::getUid()) . "'";
				break;
		}

		/*
		 * SQL queries
		 * Get data to display
		 */
		if (LoginModel::mag('P_BIEB_READ')) {
			//ingelogden
			$sSelect = "
				, GROUP_CONCAT(e.eigenaar_uid SEPARATOR ', ') AS eigenaar, GROUP_CONCAT(e.uitgeleend_uid SEPARATOR ', ') AS lener, 
				GROUP_CONCAT(e.status SEPARATOR ', ') AS status, GROUP_CONCAT(e.uitleendatum SEPARATOR ', ') AS uitleendatum, GROUP_CONCAT(e.leningen SEPARATOR ', ') AS leningen,
				( SELECT count( * ) FROM biebexemplaar e2 WHERE e2.boek_id = b.id ) AS exaantal, 
				( SELECT count( * ) FROM biebbeschrijving s2 WHERE s2.boek_id = b.id ) AS bsaantal";
			$sLeftjoin = "
				LEFT JOIN profielen l1 ON(l1.uid=e.eigenaar_uid)
				LEFT JOIN profielen l2 ON(l2.uid=e.uitgeleend_uid)";
			$sGroupby = "GROUP BY b.id";
		} else {
			//uitgelogden
			$sSelect = "";
			$sLeftjoin = "";
			$sGroupby = "";
		}

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS DISTINCT 
				b.id, b.titel, b.isbn, b.code, b.auteur, 
				CONCAT(c1.categorie, ' - ', c2.categorie) AS categorie
				" . $sSelect . "
			FROM biebboek b
			LEFT JOIN biebcategorie c2 ON(b.categorie_id = c2.id)
			LEFT JOIN biebcategorie c1 ON(c1.id = c2.p_id)
			LEFT JOIN biebexemplaar e ON(b.id = e.boek_id)
			" . $sLeftjoin . " 
			" . $sWhere . " 
			" . $sGroupby . " 
			" . $sOrder . " 
			" . $sLimit . "";

		$rResult = $db->query($sQuery) or die($sQuery . ' ' . $db->error());
		while ($aRow = $db->next_array($rResult)) {
			$this->aBoeken[] = $aRow;
		}

		/* Data set length after filtering */
		$sQuery = "
			SELECT FOUND_ROWS()";
		$rResultFilterTotal = $db->query($sQuery) or die($db->error());
		$aResultFilterTotal = $db->next_array($rResultFilterTotal);
		$this->iGefilterdTotaal = $aResultFilterTotal[0];

		/* Total data set length */
		$sQuery = "
			SELECT COUNT(id)
			FROM   biebboek";
		$rResultTotal = $db->query($sQuery) or die($db->error());
		$aResultTotal = $db->next_array($rResultTotal);
		$this->iTotaal = $aResultTotal[0];
	}

	//get info van object Catalogus
	public function getTotaal() {
		return $this->iTotaal;
	}

	public function getGefilterdTotaal() {
		return $this->iGefilterdTotaal;
	}

	public function getBoeken() {
		return $this->aBoeken;
	}

	public function getKolommen() {
		return $this->aKolommen;
	}

	public function getKolommenZichtbaar() {
		return $this->iKolommenZichtbaar;
	}

	/**
	 * geeft alle waardes in db voor $key
	 *
	 * @param $key string waarvoor waardes gezocht moeten worden
	 * @return array van alle waardes, alfabetisch gesorteerd
	 */
	public static function getAllValuesOfProperty($key) {
		$allowedkeys = array('id', 'titel', 'auteur', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code', 'naam');
		if (in_array($key, $allowedkeys)) {
			$db = MijnSqli::instance();
			if ($key == 'naam') {
				$query = "
					SELECT uid, concat(voornaam, ' ', tussenvoegsel,  IF(tussenvoegsel='','',' '), achternaam) AS naam  
					FROM profielen 
					WHERE status IN ('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID','S_ERELID') 
					ORDER BY achternaam;";
			} else {
				$query = "
					SELECT DISTINCT " . $db->escape($key) . "
					FROM biebboek
					ORDER BY " . $db->escape($key) . ";";
			}
			$result = $db->query($query);
			echo $db->error();
			if ($db->numRows($result) > 0) {
				$properties = array();
				while ($prop = $db->next($result)) {
					$properties[] = $prop[$key];
				}
				return array_filter($properties);
			}
		}
		return array();
	}

	/**
	 * @param string $zoek is kolom in tabel biebboek, of om via titel&auteur naar id's te zoeken: 'biebboek'
	 * @param $zoekterm
	 * @param int $categorie
	 * @return array json_encodeerde array(
	 *         array(data=>array(...met meuk...), value=>waarde, result=>dit komt in input na kiezen van iets in suggestielijst),
	 * array(..)
	 * )
	 * met formatItem (optie voor jquery.autocomplete) kan uit data-array inhoud worden gegenereerd voor in de li-elementen van de suggestielijst
	 */
	public static function getAutocompleteSuggesties($zoek, $zoekterm, $categorie = 0) {
		$db = MijnSqli::instance();
		$zoek = $db->escape($zoek);
		$zoekterm = $db->escape($zoekterm);
		$limiet = 5;
		if (isset($_GET['limit'])) {
			$limiet = (int)$_GET['limit'];
		}
		$wherelimit = "";
		if ((int)$limiet > 0) {
			$wherelimit = " LIMIT 0, " . (int)$limiet;
		}
		$properties = array();
		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code', 'auteur', 'biebboek');
		if (in_array($zoek, $allowedkeys)) {
			$wherecat = "";
			if ($categorie > 0) {
				$wherecat = "categorie_id = " . (int)$categorie . " AND ";
			}
			if ($zoek == 'biebboek') {
				$query = "
					SELECT titel, auteur, id, categorie_id
					FROM biebboek
					WHERE " . $wherecat . "(titel LIKE  '%" . $zoekterm . "%' OR auteur LIKE  '%" . $zoekterm . "%')
					ORDER BY titel" . $wherelimit;
				$query .= ";";
			} else {
				$query = "
					SELECT " . $zoek . ", id
					FROM biebboek
					WHERE " . $wherecat . $zoek . " LIKE  '%" . $zoekterm . "%'
					GROUP BY " . $zoek . "
					ORDER BY " . $zoek . "
					" . $wherelimit;
				$query .= ";";
			}
			$result = $db->query($query);
			echo $db->error();
			if ($db->numRows($result) > 0) {
				while ($prop = $db->next($result)) {
					if ($zoek == 'biebboek') {
						//input for UI autocomplete
						$properties[] = array('titel' => $prop['titel'], 'auteur' => $prop['auteur'], 'id' => $prop['id'], 'categorie_id' => $prop['categorie_id']);
					} else {
						if ($zoek == 'titel') {
							$data = array('titel' => $prop['titel'], 'id' => $prop['id']);
						} else {
							$data = array($prop[$zoek]);
						}
						$properties[] = array(
							'data' => $data,
							'value' => $prop[$zoek],
							'result' => $prop[$zoek]);
					}
				}
			}
		}
		return $properties;
	}

	/**
	 * Controleert of gegeven waarde voor de gegeven $key al voorkomt in de db.
	 *
	 * @param $key
	 * @param $value
	 * @return bool   true  $value bestaat in db
	 *                false $value bestaat niet
	 */
	public static function existsProperty($key, $value) {
		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code', 'auteur');
		if (in_array($key, $allowedkeys)) {
			$db = MijnSqli::instance();
			$query = "
				SELECT  " . $db->escape($key) . "
				FROM  `biebboek` 
				WHERE  `" . $db->escape($key) . "` LIKE  '" . $db->escape($value) . "'
				LIMIT 0 , 1;";
			$result = $db->query($query);
			return $db->numRows($result) > 0;
		} elseif ($key == 'rubriek') {
			return in_array($value, BiebRubriek::getAllRubriekIds());
		}
		return false;
	}

	/**
	 * Boeken opvragen van een lid
	 *
	 * @param null|string $uid een lid of leeglaten, zodat uid van ingelogd lid wordt gebruikt
	 * @param string $filter 'gerecenceerd': gerecenseerdeboeken, 'eigendom: zoekt boeken in eigendom, 'geleend': zoekt geleende boeken
	 * @return array met boeken van lid
	 */
	public static function getBoekenByUid($uid = null, $filter = 'eigendom') {
		if ($uid === null) {
			$uid = LoginModel::getUid();
		}
		$db = MijnSqli::instance();

		//bepaalt de status voor het boek, is samenvoeging van de statussen van de exemplaren
		$statusboek = ", IF(
					(SELECT count( * )
					FROM biebexemplaar e2
					WHERE e2.boek_id = b.id AND e2.status='beschikbaar'
					) > 0,
					'beschikbaar',
					IF(
						(SELECT count( * )
						FROM biebexemplaar e2
						WHERE e2.boek_id = b.id AND e2.status='teruggegeven'
						) > 0,
					'teruggegeven',
					'geen'
					)
				) AS status";

		switch ($filter) {
			case 'eigendom':
				//zoekt boeken in bezit van $uid
				$select = "$statusboek";
				$join = "biebexemplaar e ON(b.id = e.boek_id)";
				$where = "e.eigenaar_uid = '" . $db->escape($uid) . "'";
				break;
			case 'gerecenseerd':
				//zoekt boeken gerecenseerd door $uid
				$select = ", bs.beschrijving $statusboek";
				$join = "biebbeschrijving bs ON(b.id = bs.boek_id)";
				$where = "bs.schrijver_uid = '" . $db->escape($uid) . "'";
				break;
			case 'geleend':
				//zoekt boeken geleend door $uid
				$select = ", e.status AS status, e.eigenaar_uid";
				$join = "biebexemplaar e ON(b.id = e.boek_id)";
				$where = "(e.status =  'uitgeleend' OR e.status =  'teruggegeven') " .
					"AND e.uitgeleend_uid =  '" . $db->escape($uid) . "'";
				break;
			default:
				return false;
		}

		$query = "
			SELECT DISTINCT 
				b.id, b.titel, b.auteur $select
			FROM biebboek b
			LEFT JOIN $join
			WHERE  $where
			" . ($filter == 'geleend' ? "" : "GROUP BY b.id") . "
			ORDER BY titel;";
		$result = $db->query($query);

		if ($db->numRows($result) > 0) {
			$boeken = array();
			while ($boek = $db->next($result)) {
				$boeken[] = $boek;
			}
			return $boeken;
		} else {
			return false;
		}
	}

}
