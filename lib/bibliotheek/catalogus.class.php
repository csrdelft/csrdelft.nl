<?php
/*
 * Catalogus
 *
 *
 */

class Catalogus{

	private $boeken=null;
	private $filter;

	public function __construct($init){
		$allow=array('alle', 'csr', 'leden', 'eigen', 'geleend');
		if(in_array($init, $allow)){
			$this->filter=$init;
		}else{
			$this->filter='csr';
		}
	}

	/*
	 * De boeken ophalen. 
	 * Boeken worden niet automagisch geladen, enkel bij het opvragen
	 * voor auteur en categorie worden een string opgeslagen ipv id's
	 * via count() of getDocumenten()
	 */
	public function loadBoeken(){
		$db=MySql::instance();
		switch($this->filter){
			case 'csr':
				$where = "WHERE e.eigenaar_uid='x222'";
				break;
			case 'leden':
				$where = "WHERE e.eigenaar_uid NOT LIKE 'x222'";
				break;
			default:
				$where = "";
				break;
		}
		$query="
			SELECT DISTINCT
				b.id , b.titel , b.uitgavejaar , b.uitgeverij , b.paginas, 
				b.taal, b.isbn, b.code, a.auteur,
				CONCAT(c1.categorie, ',',
					c2.categorie, ',',
					c3.categorie ) AS categorie,
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
				'geen')) AS status
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
		echo mysql_error();
		if($db->numRows($result)>0){
			while($boek=$db->next($result)){
				$this->boeken[]=new Boek($boek);
			}
		}else{
			return false;
		}
		return $db->numRows($result);
	}

	public function getBoeken($force=false){
		if($this->boeken===null OR $force){
			$this->loadBoeken();
		}
		return $this->boeken; 
	}
	public function count(){
		if($this->boeken===null){
			$this->loadBoeken();
		}
		return count($this->boeken);
	}

	// retourneert filter
	public function getFilter(){
		return $this->filter;
	}

	/*********************
	 * Beheer van boeken *
	 *********************/
	public function loadBeheerBoeken(){
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
		$query="
			SELECT
				b.id AS bkid , b.titel , b.uitgavejaar , b.uitgeverij , b.paginas, 
				b.taal, b.isbn, b.code, a.auteur,
				CONCAT(c1.categorie, ' - ',
					c2.categorie, ' - ',
					c3.categorie ) AS categorie, c3.categorie AS cat,
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
				) AS bsaantal
			FROM 
				biebboek b
			
			LEFT JOIN biebauteur a        ON(b.auteur_id = a.id)
			LEFT JOIN biebcategorie c3    ON(b.categorie_id = c3.id)
			LEFT JOIN biebcategorie c2    ON(c2.id = c3.p_id)
			LEFT JOIN biebcategorie c1    ON(c1.id = c2.p_id)
			LEFT JOIN biebexemplaar e     ON(b.id = e.boek_id)
			".$where."
			ORDER BY b.titel DESC;";
		$result=$db->query($query);
		echo mysql_error();

		//nu een beetje magic om een stapeltje met boeken te genereren:
		$aBoek=array('bkid'=>null);
		$exemplaren=array();
		$boekeigenschappen=array(
					'bkid', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 
					'taal', 'isbn', 'code', 'auteur', 'categorie','cat', 'exaantal', 'bsaantal');
		while($aBoekraw=$db->next($result)){
			//eerste boekgegevens bewaren
			if($aBoek['bkid']===null){
				$aBoek=array_get_keys($aBoekraw, $boekeigenschappen);
			}

			//zijn we bij een volgende boek aangekomen?
			if($aBoek['bkid']!=$aBoekraw['bkid']){
				//exemplaren bij boekgegevens stoppen en aan de array toevoegen
				$aBoek['exemplaren'] = $exemplaren;
				$this->boeken[$aBoek['bkid']]=$aBoek;

				//tenslotte het volgende boek bewaren
				$aBoek=array_get_keys($aBoekraw, $boekeigenschappen);
				$exemplaren=array();
			}
			$exemplaren[]=array_get_keys($aBoekraw, array('exid','eigenaar','lener','uitleendatum','status'));
		}
		if(isset($aBoek['bkid'])){
			//tot slot het laatste boek ook toevoegen
			$aBoek['exemplaren'] = $exemplaren;
			$this->boeken[$aBoek['bkid']]=$aBoek;
		}
	}

	public function getBeheerboeken(){
		if($this->boeken===null){
			$this->loadBeheerBoeken();
		}
		return $this->boeken; 
	}

	public static function getAllValuesOfProperty($key){
		$allowedkeys = array('id', 'titel', 'uitgavejaar', 'uitgeverij', 'paginas', 'taal', 'isbn', 'code');
		if(in_array($key, $allowedkeys)){
			$db=MySql::instance();
			$query="
				SELECT DISTINCT ".$db->escape($key)."
				FROM biebboek;";
			$result=$db->query($query);
			echo mysql_error();
			if($db->numRows($result)>0){
				while($prop=$db->next($result)){
					$properties[]=$prop[$key];
				}
				sort($properties);
				return array_filter($properties);
			}
		}
		return array();
	}

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
