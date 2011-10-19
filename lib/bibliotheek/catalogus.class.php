<?php
/*
 * Catalogus
 *
 *
 */

class Catalogus{

	/*
	 * Zet json in elkaar voor dataTables om catalogus of boekstatus tabel mee te vullen
	 * 
	 * @param $exemplaarinfo false: laden voor catalogus, true: laden voor boekstatuspagina
	 * @return json
	 */
	static public function getJSONcatalogusdata($exemplaarinfo=false){
		/*
		 * Script:    DataTables server-side script for PHP and MySQL
		 * Copyright: 2010 - Allan Jardine
		 * License:   GPL v2 or BSD (3-point)
		 */

		// kolommen van de tabel. De laatste velden die niet in tabel staan worden gebruik om op te filteren.
		if($exemplaarinfo){
			//boekstatus
			$aColumns = array( 'titel', 'code', 'bsaantal', 'eigenaar', 'lener', 'status', 'leningen', 'isbn', 'auteur', 'categorie');
			$iColumnsZichtbaar = 7;
		}else{
			//catalogus
			$aColumns = array( 'titel', 'auteur', 'categorie', 'code', 'isbn');
			$iColumnsZichtbaar = 3;
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
		$aSortColumns = array('titel'=>"titel", 'auteur'=>"auteur", 'categorie'=>"categorie",'code'=>"code", 'isbn'=>"isbn", 'bsaantal'=>"bsaantal", 
			'status'=>"status", 'leningen'=>"leningen", 'eigenaar'=>"eigenaar", 'lener'=>"lener");

		//is er een kolom gegeven om op te sorteren?
		if( isset($_GET['iSortCol_0']) ){
			$sOrder = "ORDER BY ";
			//loop als er op meer kolommen tegelijk gesorteerd moet worden
			for( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ){
				//mag kolom gesorteerd worden?
				if( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" ){
					$sOrder .= $aSortColumns[$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]]." ".$db->escape( $_GET['sSortDir_'.$i] ) .", ";
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
		$aFilterColumns = array('titel'=> "titel", 'auteur'=>"auteur", 'categorie'=>"CONCAT(c1.categorie, ' - ', c2.categorie, ' - ', c3.categorie )",
			'code'=>"code", 'isbn'=>"isbn", 'bsaantal'=>"bsaantal", 'status'=>"e.status", 'leningen'=>"leningen", 
			'eigenaar'=>"CONCAT(l1.voornaam, ' ', l1.tussenvoegsel,IFNULL(l1.tussenvoegsel, ' '),l1.achternaam)", 
			'lener'=> "CONCAT(l2.voornaam, ' ',l2.tussenvoegsel, IFNULL(l1.tussenvoegsel, ' '),l2.achternaam)");

		$sWhere = "";
		if( $_GET['sSearch'] != "" ){
			$sWhere = "WHERE (";
			for( $i=0 ; $i<count($aColumns) ; $i++ ){
				//beschrijvingenaantal skippen, die heeft een subquery nodig
				if($aColumns[$i]!='bsaantal'){
					$sWhere .= $aFilterColumns[$aColumns[$i]]." LIKE '%".$db->escape( $_GET['sSearch'] )."%' OR ";
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
		if(in_array($_GET['sFilter'], $allow)){
			$filter = $_GET['sFilter'];
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
		if($exemplaarinfo){
			//boekstatus
			$sSelect = "
				GROUP_CONCAT(e.eigenaar_uid SEPARATOR ', ') AS eigenaar, GROUP_CONCAT(e.uitgeleend_uid SEPARATOR ', ') AS lener, 
				GROUP_CONCAT(e.status SEPARATOR ', ') AS status, GROUP_CONCAT(e.uitleendatum SEPARATOR ', ') AS uitleendatum, GROUP_CONCAT(e.leningen SEPARATOR ', ') AS leningen,
				( SELECT count( * ) FROM biebexemplaar e2 WHERE e2.boek_id = b.id ) AS exaantal, 
				( SELECT count( * ) FROM biebbeschrijving s2 WHERE s2.boek_id = b.id ) AS bsaantal";
			$sLeftjoin = "
				LEFT JOIN lid l1 ON(l1.uid=e.eigenaar_uid)
				LEFT JOIN lid l2 ON(l2.uid=e.uitgeleend_uid)";
			$sGroupby = "GROUP BY b.id";
		}else{
			//catalogus
			$sSelect = "
				IF((
					SELECT count( * )
					FROM biebexemplaar e2
					WHERE e2.boek_id = b.id AND e2.status='beschikbaar'
					) > 0, 
				'beschikbaar', 
					IF((
						SELECT count( * )
						FROM biebexemplaar e2
						WHERE e2.boek_id = b.id AND e2.status='teruggegeven'
						) > 0,
					'teruggegeven',
					'geen'
					)
				) AS status";
			$sLeftjoin = "";
			$sGroupby = "";
		}

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS DISTINCT 
				b.id, b.titel, b.isbn, b.code, a.auteur, 
				CONCAT(c1.categorie, ' - ', c2.categorie, ' - ', c3.categorie ) AS categorie,
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
		
		/* Data set length after filtering */
		$sQuery = "
			SELECT FOUND_ROWS()";
		$rResultFilterTotal = $db->query( $sQuery ) or die(mysql_error());
		$aResultFilterTotal = $db->next_array($rResultFilterTotal);
		$iFilteredTotal = $aResultFilterTotal[0];
		
		/* Total data set length */
		$sQuery = "
			SELECT COUNT(id)
			FROM   biebboek";
		$rResultTotal = $db->query( $sQuery ) or die(mysql_error());
		$aResultTotal = $db->next_array($rResultTotal);
		$iTotal = $aResultTotal[0];


		/*
		 * Output
		 */
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);

		while($aRow = $db->next_array($rResult) ){
			$row = array();
			//loopt over de zichtbare kolommen
			for($i=0 ; $i<$iColumnsZichtbaar ; $i++ ){
				//van sommige kolommen wordt de inhoud verfraaid
				switch($aColumns[$i]){
					case 'titel':
						//statusindicator op cataloguspagina en title van url
						if($exemplaarinfo){
							//boekstatus
							$titel = '';
							$urltitle = 'title="Boek: '.$aRow['titel'].'
Auteur: '.$aRow['auteur'].' 
Rubriek: '.$aRow['categorie'].'"';
						}else{
							//catalogus
							$titel = '<span title="'.$aRow['status'].' boek" class="indicator '.$aRow['status'].'">•</span> ';
							$urltitle = 'title="Boek bekijken"';
						}
						//url
						if(Loginlid::instance()->hasPermission('P_BIEB_READ')){
							$titel .= '<a href="/communicatie/bibliotheek/boek/'.$aRow['id'].'" '.$urltitle.'>'.htmlspecialchars($aRow['titel']).'</a>';
						}else{
							$titel .= htmlspecialchars($aRow['titel']);
						}
						$row[] = $titel;
						break;
					case 'eigenaar':
					case 'lener':
						$aUid = explode(', ', $aRow[$aColumns[$i]]);
						$naamlijst = '';
						foreach( $aUid as $uid ){
							if($uid == 'x222'){
								$naamlijst .= 'C.S.R.-bibliotheek';
							}else{
								if(Lid::isValidUid($uid)){
								$lid=LidCache::getLid($uid);
									if($lid instanceof Lid){
										$naamlijst .= $lid->getNaamLink('civitas', 'link');
									}else{
										$naamlijst .= '-';
									}
								}else{
									$naamlijst .= '-';
								}
							}
							$naamlijst .= '<br />';
						}
						$row[] = $naamlijst;
						break;
					case 'status':
						$aStatus = explode(', ', $aRow['status']);
						$aUitleendatum = explode(', ', $aRow['uitleendatum']);
						$statuslijst = '';
						$j=0;
						foreach( $aStatus as $status ){
							if($status == 'uitgeleend' OR  $status == 'teruggegeven'){
								$statuslijst .= '<span title="Uitgeleend sinds '.strip_tags(reldate($aUitleendatum[$j])).'">'.ucfirst($status).'</span>';
							}elseif($status == 'vermist'){
								$statuslijst .= '<span title="Vermist sinds '.strip_tags(reldate($aRow[$aUitleendatum[$j]])).'">'.ucfirst($status).'</span>';
							}else{
								$statuslijst .= ucfirst($status);
							}
							$statuslijst .= '<br />';
							$j++;
						}
						$row[] = $statuslijst;
						break;
					case 'leningen':
						$row[] = str_replace(', ', '<br />', $aRow['leningen']);
						break;
					default:
						$row[] = htmlspecialchars($aRow[ $aColumns[$i] ]);
				}
			}
			$output['aaData'][] = $row;
		}

		return json_encode( $output );
	}

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

}
