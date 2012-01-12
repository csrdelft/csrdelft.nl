<?php
/*
 * Catalogus
 *
 *
 */

class Catalogus{

private $aBoeken = array();
private $iTotaal;
private $iGefilterdTotaal;
private $aKolommen = array(); // kolommen van de tabel. De laatste velden die niet in tabel staan worden gebruik om op te filteren.
private $iKolommenZichtbaar; //aantal kolommen zichtbaar in de tabel.

	public function __construct(){
		$this->loadCatalogusdata();
	}

	/*
	 * Zet json in elkaar voor dataTables om catalogustabel mee te vullen
	 * Filters en sortering worden aan de hand van parameters uit _GET ingesteld
	 * 
	 * @return json
	 */
	protected function loadCatalogusdata(){
		/*
		 * Script:    DataTables server-side script for PHP and MySQL
		 * Copyright: 2010 - Allan Jardine
		 * License:   GPL v2 or BSD (3-point)
		 */


		// kolommen van de tabel. De laatste velden die niet in tabel staan worden gebruik om op te filteren.
		if(LoginLid::instance()->hasPermission('P_BIEB_READ')){
			//boekstatus
			$this->aKolommen = array( 'titel', 'auteur', 'categorie', 'bsaantal', 'eigenaar', 'lener', 'uitleendatum', 'status', 'code', 'isbn', 'auteur', 'categorie');
			$this->iKolommenZichtbaar = 7;
		}else{
			//catalogus
			$this->aKolommen = array( 'titel', 'auteur', 'categorie', 'code', 'isbn');
			$this->iKolommenZichtbaar = 3;
		}

		/* MySQL */
		$db=MySql::instance();

		/* 
		 * Paging
		 */
		$sLimit = "";
		if( isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1' ){
			$sLimit = "LIMIT ".(int) $_GET['iDisplayStart'].", ".(int) $_GET['iDisplayLength'];
		}


		/*
		 * Ordering
		 */

		//sorteerkeys voor mysql
		$aSortColumns = array(	'titel'=>"titel", 	'auteur'=>"auteur", 	'categorie'=>"categorie",
								'code'=>"code", 	'isbn'=>"isbn", 		'bsaantal'=>"bsaantal", 
								'status'=>"status", 'leningen'=>"leningen", 'eigenaar'=>"eigenaar", 
								'lener'=>"lener", 'uitleendatum'=>"uitleendatum");

		//is er een kolom gegeven om op te sorteren?
		if( isset($_GET['iSortCol_0']) ){
			$sOrder = "ORDER BY ";
			//loop als er op meer kolommen tegelijk gesorteerd moet worden
			for( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ){
				//mag kolom gesorteerd worden?
				if( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ){
					$sOrder .= $aSortColumns[$this->aKolommen[ intval( $_GET['iSortCol_'.$i] ) ]]." ".$db->escape( $_GET['sSortDir_'.$i] ) .", ";
				}
			}
			$sOrder = substr_replace( $sOrder, "", -2 );

			if( $sOrder == "ORDER BY " ){
				$sOrder = "";
			}
		}


		/* 
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		//sorteer key voor mysql
		$aFilterColumns = array('titel'=> "titel", 	'auteur'=>"auteur", 'categorie'=>"CONCAT(c1.categorie, ' - ', c2.categorie, ' - ', c3.categorie )",
								'code'=>"code", 	'isbn'=>"isbn", 	'bsaantal'=>"bsaantal", 'status'=>"e.status", 'leningen'=>"leningen", 
								'eigenaar'=>"CONCAT(l1.voornaam, ' ', l1.tussenvoegsel,IFNULL(l1.tussenvoegsel, ' '),l1.achternaam)", 
								'lener'=> "CONCAT(l2.voornaam, ' ',l2.tussenvoegsel, IFNULL(l1.tussenvoegsel, ' '),l2.achternaam)",
								'uitleendatum'=>"uitleendatum");

		$sWhere = "";
		if( $_GET['sSearch'] != "" ){
			$sWhere = "WHERE (";
			for( $i=0 ; $i<count($this->aKolommen) ; $i++ ){
				//beschrijvingenaantal skippen, die heeft een subquery nodig
				if($this->aKolommen[$i]!='bsaantal'){
					$sWhere .= $aFilterColumns[$this->aKolommen[$i]]." LIKE '%".$db->escape( $_GET['sSearch'] )."%' OR ";
				}
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}

		/* 
		 * filter op eigenaar
		 */

		//filter bepalen
		$allow = array('alle', 'csr', 'leden', 'eigen', 'geleend');
		if(LoginLid::instance()->hasPermission('P_BIEB_READ') AND in_array($_GET['sEigenaarFilter'], $allow)){
			$filter = $_GET['sEigenaarFilter'];
		}else{
			$filter = 'csr';
		}

		if($sWhere == ""){
			$sBeginWh = "WHERE ";
		}else{
			$sBeginWh = " AND ";
		}
		switch($filter){
			case 'csr':
				$sWhere .= $sBeginWh."e.eigenaar_uid='x222'";
				break;
			case 'leden':
				$sWhere .= $sBeginWh."e.eigenaar_uid NOT LIKE 'x222'";
				break;
			case 'eigen':
				$sWhere .= $sBeginWh."e.eigenaar_uid='".$db->escape(Loginlid::instance()->getUid())."'";
				break;
			case 'geleend':
				$sWhere .= $sBeginWh."(e.status = 'uitgeleend' OR e.status = 'teruggegeven')AND e.uitgeleend_uid='".$db->escape(Loginlid::instance()->getUid())."'";
				break;
		}

		/*
		 * SQL queries
		 * Get data to display
		 */
		if(LoginLid::instance()->hasPermission('P_BIEB_READ')){
			//ingelogden
			$sSelect = "
				, GROUP_CONCAT(e.eigenaar_uid SEPARATOR ', ') AS eigenaar, GROUP_CONCAT(e.uitgeleend_uid SEPARATOR ', ') AS lener, 
				GROUP_CONCAT(e.status SEPARATOR ', ') AS status, GROUP_CONCAT(e.uitleendatum SEPARATOR ', ') AS uitleendatum, GROUP_CONCAT(e.leningen SEPARATOR ', ') AS leningen,
				( SELECT count( * ) FROM biebexemplaar e2 WHERE e2.boek_id = b.id ) AS exaantal, 
				( SELECT count( * ) FROM biebbeschrijving s2 WHERE s2.boek_id = b.id ) AS bsaantal";
			$sLeftjoin = "
				LEFT JOIN lid l1 ON(l1.uid=e.eigenaar_uid)
				LEFT JOIN lid l2 ON(l2.uid=e.uitgeleend_uid)";
			$sGroupby = "GROUP BY b.id";
		}else{
			//uitgelogden
			$sSelect = "";
			$sLeftjoin = "";
			$sGroupby = "";
		}

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS DISTINCT 
				b.id, b.titel, b.isbn, b.code, a.auteur, 
				CONCAT(c1.categorie, ' - ', c2.categorie, ' - ', c3.categorie ) AS categorie
				".$sSelect."
			FROM biebboek b
			LEFT JOIN biebauteur a ON(b.auteur_id = a.id)
			LEFT JOIN biebcategorie c3 ON(b.categorie_id = c3.id)
			LEFT JOIN biebcategorie c2 ON(c2.id = c3.p_id)
			LEFT JOIN biebcategorie c1 ON(c1.id = c2.p_id)
			LEFT JOIN biebexemplaar e ON(b.id = e.boek_id)
			".$sLeftjoin." 
			".$sWhere." 
			".$sGroupby." 
			".$sOrder." 
			".$sLimit."";

		$rResult = $db->query($sQuery) or die($sQuery.' '.mysql_error());
		while($aRow = $db->next_array($rResult) ){
			$this->aBoeken[] = $aRow;
		}

		/* Data set length after filtering */
		$sQuery = "
			SELECT FOUND_ROWS()";
		$rResultFilterTotal = $db->query( $sQuery ) or die(mysql_error());
		$aResultFilterTotal = $db->next_array($rResultFilterTotal);
		$this->iGefilterdTotaal = $aResultFilterTotal[0];

		/* Total data set length */
		$sQuery = "
			SELECT COUNT(id)
			FROM   biebboek";
		$rResultTotal = $db->query( $sQuery ) or die(mysql_error());
		$aResultTotal = $db->next_array($rResultTotal);
		$this->iTotaal = $aResultTotal[0];

	}

	//get info van object Catalogus
	public function getTotaal()				{ return $this->iTotaal; }
	public function getGefilterdTotaal()	{ return $this->iGefilterdTotaal; }
	public function getBoeken()				{ return $this->aBoeken; }
	public function getKolommen()			{ return $this->aKolommen; }
	public function getKolommenZichtbaar()	{ return $this->iKolommenZichtbaar; }


	/*
	 * geeft alle waardes in db voor $key
	 * 
	 * @param $key waarvoor waardes gezocht moeten worden
	 * @return array van alle waardes, alfabetisch gesorteerd
	 */
	public static function getAllValuesOfProperty($key){
		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code', 'naam');
		if(in_array($key, $allowedkeys)){
			$db=MySql::instance();
			if($key=='naam'){
				$query="
					SELECT uid, concat(voornaam, ' ', tussenvoegsel,  IF(tussenvoegsel='','',' '), achternaam) as naam  
					FROM lid 
					WHERE status IN ('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID','S_ERELID') 
					ORDER BY achternaam;";
			}else{
				$query="
					SELECT DISTINCT ".$db->escape($key)."
					FROM biebboek
					ORDER BY ".$db->escape($key).";";
			}
			$result=$db->query($query);
			echo mysql_error();
			if($db->numRows($result)>0){
				while($prop=$db->next($result)){
					$properties[]=$prop[$key];
				}
				return array_filter($properties);
			}
		}
		return array();
	}

	/*
	 * controleert of gegeven waarde voor de gegeven $key al voorkomt in de db.
	 * 
	 * @param $key en $value
	 * @return	true $value bestaat in db
	 * 			false $value bestaat niet
	 */
	public static function existsProperty($key,$value){
		$return = false;
		switch ($key) {
			case 'titel':
			case 'isbn':
				$return = in_array($value, Catalogus::getAllValuesOfProperty($key));
				break;
			case 'rubriek':
				$return = in_array($value, Rubriek::getAllRubriekIds());
				break;
			case 'auteur':
				$return = in_array($value, Auteur::getAllAuteurIds());
				break;
		}
		return $return;
	}

	/* 
	 * Boeken opvragen van een lid
	 * 
	 * @param 	uid van een lid of leeglaten, zodat uid van ingelogd lid wordt gebruikt
	 * 			gerecenseerdeboeken: false zoekt boeken in eigendom, true zoekt gerecenseerde boeken
	 * @return array met boeken van lid
	 */
	public static function getBoekenByUid($uid=null,$gerecenseerdeboeken=false){
		if($uid===null){
			$uid=LoginLid::instance()->getUid();
		}
		$db=MySql::instance();
		if($gerecenseerdeboeken){
			//zoekt boeken gerecenseerd door $uid
			$select = " bs.beschrijving,";
			$join = "biebbeschrijving bs ON(b.id = bs.boek_id)";
			$where = "bs.schrijver_uid = '".$db->escape($uid)."'";
		}else{
			//zoekt boeken in bezit van $uid
			$select = "";
			$join = "biebexemplaar e ON(b.id = e.boek_id)";
			$where = "e.eigenaar_uid = '".$db->escape($uid)."'";
		}
		$query="
			SELECT DISTINCT 
				b.id, b.titel, a.auteur,".$select."
				IF(
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
				) AS status
			FROM biebboek b
			LEFT JOIN biebauteur a ON(b.auteur_id = a.id)
			LEFT JOIN ".$join."
			WHERE ".$where."
			GROUP BY b.id
			ORDER BY titel;";

		$result=$db->query($query);

		if($db->numRows($result)>0){
			while($boek=$db->next($result)){
				$boeken[] = $boek;
			}
			return $boeken;
		}else{
			return false;
		}
	}

}
