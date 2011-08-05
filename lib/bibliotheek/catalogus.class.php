<?php
/*
 * Catalogus
 *
 *
 */

class Catalogus{

	private $boeken=null;
	private $filter; 		//het gewenste filter

	public function __construct($init){
		$allow=array('alle', 'csr', 'leden', 'eigen', 'geleend');
		if(in_array($init, $allow)){
			$this->filter=$init;
		}else{
			$this->filter='csr';
		}
	}

	/*
	 * Laad boeken in object.
	 * Voor de catalogus
	 *
	 * @param $catalogus true: laden voor catalogus, false: laden voor beheerpagina
	 * @return void
	 */
	public function loadBoeken($catalogus=true){
		$db=MySql::instance();
		switch($this->filter){
			case 'csr':
				$where = "WHERE e.eigenaar_uid='x222'";
				break;
			case 'leden':
				$where = "WHERE e.eigenaar_uid NOT LIKE 'x222'";
				break;
			case 'eigen':
				$where = "WHERE e.eigenaar_uid='".$db->escape(Loginlid::instance()->getUid())."'";
				break;
			case 'geleend':
				$where = "WHERE (e.status = 'uitgeleend' OR e.status = 'teruggegeven')AND e.uitgeleend_uid='".$db->escape(Loginlid::instance()->getUid())."'";
				break;
			default:
				$where = "";
				break;
		}

		if($catalogus){ //catalogus
			$queryselect = "
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
				) AS status
			";
		}else{ //beheer
			$queryselect = "
				e.id AS exid, e.eigenaar_uid AS eigenaar ,e.uitgeleend_uid AS lener, 
				e.status, e.uitleendatum, 
				(
					SELECT count( * )
					FROM biebexemplaar e2
					WHERE e2.boek_id = b.id
				) AS exaantal, 
				(
					SELECT count( * )
					FROM biebbeschrijving s2
					WHERE s2.boek_id = b.id
				) AS bsaantal ";
		}

		$query="
			SELECT DISTINCT
				b.id , b.titel , b.uitgavejaar , b.uitgeverij , b.paginas, 
				b.taal, b.isbn, b.code, a.auteur,
				CONCAT(c1.categorie, ' - ',
					c2.categorie, ' - ',
					c3.categorie ) AS categorie,
				".$queryselect."
				
			FROM 
				biebboek b
			
			LEFT JOIN biebauteur a     ON(b.auteur_id = a.id)
			LEFT JOIN biebcategorie c3 ON(b.categorie_id = c3.id)
			LEFT JOIN biebcategorie c2 ON(c2.id = c3.p_id)
			LEFT JOIN biebcategorie c1 ON(c1.id = c2.p_id)
			LEFT JOIN biebexemplaar e  ON(b.id = e.boek_id)
			".$where."
			ORDER BY titel DESC;";
		$result=$db->query($query);

		if($catalogus){ //catalogus
			if($db->numRows($result)>0){
				while($boek=$db->next($result)){
					$this->boeken[]=new Boek($boek);
				}
			}else{
				echo mysql_error();
			}

		}else{ //beheer

			if($db->numRows($result)>0){
				//nu een beetje magic om een stapeltje met boeken te genereren:
				$aBoek=array('id'=>null);
				$exemplaren=array();
				$boekeigenschappen=array(
							'id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 
							'taal', 'isbn', 'code', 'auteur', 'categorie', 'exaantal', 'bsaantal');
				while($aBoekraw=$db->next($result)){
					//eerste boekgegevens bewaren
					if($aBoek['id']===null){
						$aBoek=array_get_keys($aBoekraw, $boekeigenschappen);
					}

					//zijn we bij een volgende boek aangekomen?
					if($aBoek['id']!=$aBoekraw['id']){
						//exemplaren bij boekgegevens stoppen en aan de array toevoegen
						$aBoek['exemplaren'] = $exemplaren;
						$this->boeken[$aBoek['id']]=$aBoek;

						//tenslotte het volgende boek bewaren
						$aBoek=array_get_keys($aBoekraw, $boekeigenschappen);
						$exemplaren=array();
					}
					$exemplaren[]=array_get_keys($aBoekraw, array('exid','eigenaar','lener','uitleendatum','status'));
				}
				if(isset($aBoek['id'])){
					//tot slot het laatste boek ook toevoegen
					$aBoek['exemplaren'] = $exemplaren;
					$this->boeken[$aBoek['id']]=$aBoek;
					
				}
			}else{
				echo mysql_error();
			}

		}
	}
	/*
	 * Geeft alle boeken
	 * 
	 * @param bool $catalogus true: catalogus, false: beheerpagina
	 * @return 
	 *		$catalogus=true: array Boek objecten
	 *		$catalogus=false: array Boeken en subarrays van exemplaren
	 */
	public function getBoeken($catalogus, $force=false){
		if($this->boeken===null OR $force){
			$this->loadBoeken($catalogus);
		}
		return $this->boeken; 
	}
	/*
	 * Telt aantal boeken in object
	 * 
	 * @param bool $catalogus: true: catalogus, false: beheerpagina
	 * @return int aantal boeken
	 */
	public function count($catalogus){
		if($this->boeken===null){
			$this->loadBoeken($catalogus);
		}
		return count($this->boeken);
	}

	// retourneert filter
	public function getFilter(){
		return $this->filter;
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
