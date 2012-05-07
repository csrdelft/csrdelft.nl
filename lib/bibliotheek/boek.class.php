<?php
/*
 * boek.class.php	| 	Gerrit Uitslag
 *
 * boeken
 *
 */
require_once 'rubriek.class.php';
require_once 'formulier.class.php';

class Boek{

	private $id=0;			//boekId
	private $titel;			//String
	private $auteur;		//String Auteur
	private $rubriek=null;	//Rubriek object
	private $uitgavejaar;
	private $uitgeverij;
	private $paginas;
	private $taal='Nederlands';
	private $isbn;
	private $code;

	private $status;				//'beschikbaar'/'teruggeven'/'geen'
	private $biebboek = 'nee';		//'ja'/'nee'
	private $error = '';
	private $nieuwboekform;			// Form objecten voor nieuwboekformulier
	private $boekbeschrijvingform;	// Form objecten voor recensieformulier
	private $editablefieldsform;	// Form objecten info v. boek
	private $beschrijving;			// recensie tijdens toevoegen/bewerken
	private $beschrijvingsid;		// id van recensie 
	private $beschrijvingen = null;	// array
	private $exemplaren = null;		// array

	public function __construct($init){
		$this->load($init);
	}
	/*
	 * Laad object Boek afhankelijk van parameters van de constructor
	 * 
	 * @param	$array met eigenschappen	integer boekId of boekId = 0
	 * @return	void
	 */
	public function load($init=0){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$this->id=(int)$init;
			if($this->getId()==0){
				//Bij $this->id==0 gaat het om een nieuw boek. Hier
				//zetten we de defaultwaarden voor het nieuwe boek.
				$this->rubriek = new Rubriek(108);
				if($this->isBASFCie()){
					$this->biebboek = 'ja';
				}
				$this->assignFieldsNieuwboekForm();
			}else{
				$db=MySql::instance();
				$query="
					SELECT id, titel, auteur, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code,
					IF((
						SELECT count( * )
						FROM biebexemplaar e2
						WHERE e2.boek_id = biebboek.id AND e2.status='beschikbaar'
						) > 0, 
					'beschikbaar', 
						IF((
							SELECT count( * )
							FROM biebexemplaar e2
							WHERE e2.boek_id = biebboek.id AND e2.status='teruggegeven'
							) > 0,
						'teruggegeven',
						'geen'
						)
					) AS status
					FROM biebboek
					WHERE Id=".$this->getId().";";
				$boek=$db->getRow($query);
				if(is_array($boek)){
					$this->array2properties($boek);
					$this->assignFieldsBeschrijvingForm($bewerken=false);
					$this->assignAjaxFieldsForm();
				}else{
					throw new Exception('load() mislukt. Bestaat het boek wel? '.mysql_error());
				}
			}
		}

	}
	/*
	 * Eigenschappen in object stoppen
	 * @param	array met eigenschappen, setValue() moet de keys kennen
	 * @return	void
	 */ 
	private function array2properties($properties){
		foreach ($properties as $prop => $value){
			$this->setValue($prop, $value);
		}
	}

	public function getId(){			return $this->id;}
	public function getTitel(){			return $this->titel;}
	public function getUitgavejaar(){	return $this->uitgavejaar;}
	public function getUitgeverij(){	return $this->uitgeverij;}
	public function getPaginas(){		return $this->paginas;}
	public function getTaal(){			return $this->taal;}
	public function getISBN(){			return $this->isbn;}
	public function getCode(){			return $this->code;}
	public function getAuteur(){		return $this->auteur;}
	//retourneert object
	public function getRubriek(){		return $this->rubriek;}

	/*
	 * retourneert strings.
	 * @param $entry string eigenschapnaam, waarbij leners en opmerkingen ook exemplaarid bevatten 
	 * @return string waarde zals in object opgeslagen
	 */
	public function getProperty($entry){
		//$entry voor leners eerst opsplitsen
		if(substr($entry,0,6)=='lener_'){
			$exemplaarid=substr($entry,6);
			$entry='lener';
		}elseif(substr($entry,0,10)=='opmerking_'){
			$exemplaarid = substr($entry,10);
			$entry='opmerking';
		}

		switch($entry){
			case 'rubriek':
				$return = $this->getRubriek()->getId();
				break;
			case 'rubriekid':
				$return = $this->getRubriek()->getId();
				break;
			case 'titel':
			case 'uitgavejaar':
			case 'uitgeverij':
			case 'paginas':
			case 'taal':
			case 'isbn':
			case 'code':
			case 'auteur':
				$return = $this->$entry;
				break;
			case 'lener':
				$uid=$this->exemplaren[$exemplaarid]['uitgeleend_uid'];
				$lid=LidCache::getLid($uid);
				if($lid instanceof Lid){
					$return = $lid->getNaamLink('civitas', 'link');
				}else{
					$return = 'Geen geldig lid getProperty';
				}
				break;
			case 'opmerking':
				$return = $this->exemplaren[$exemplaarid]['opmerking'];
				break;
			default:
				return 'entry "'.$entry.'" is niet toegestaan. Boek::getProperty()';
		}
		return htmlspecialchars($return);
	}
	//geeft beschikbaarheid van boek
	public function getStatus(){
		return $this->status;
	}
	//geeft opgeslagen fouten
	public function getError(){
		return $this->error;
	}
	//url naar dit boek
	public function getUrl(){
		return CSR_ROOT.'communicatie/bibliotheek/boek/'.$this->getId();
	}
	/* 
	 * set gegeven waardes in Boek
	 * @param	$key moet bekend zijn, anders exception
	 * @return	void
	 */
	public function setValue($key, $value){
		//$key voor leners en opmerkingen eerst opsplitsen
		if(substr($key,0,6)=='lener_'){
			$exemplaarid = substr($key,6);
			$key='lener';
		}elseif(substr($key,0,10)=='opmerking_'){
			$exemplaarid = substr($key,10);
			$key='opmerking';
		}

		switch ($key) {
			//integers
			case 'id':
			case 'uitgavejaar':
			case 'paginas':
				$this->$key=(int)trim($value);
				break;
			//strings
			case 'categorie':
				$this->rubriek = new Rubriek(explode(' - ' , $value));
				break;
			case 'categorie_id':
			case 'rubriek':
				try{
					$this->rubriek = new Rubriek($value);
				}catch(Exception $e){
					throw new Exception($e->getMessage().' Boek::setValue "'.$key.'"');
				}
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'code':
			case 'isbn':
			case 'status':
			case 'auteur':
				$this->$key=trim($value);
				break;
			case 'beschrijving':
				$this->beschrijving=$value;
				break;
			case 'beschrijvingsid':
				$this->beschrijvingsid=$value;
				break;
			case 'biebboek':
				$this->biebboek=$value;
				break;
			case 'lener':
				$zoekin=array('S_LID', 'S_NOVIET', 'S_GASTLID', 'S_KRINGEL', 'S_OUDLID','S_ERELID');
				$uid=namen2uid($value, $zoekin);
				$this->exemplaren[$exemplaarid]['uitgeleend_uid']=$uid[0]['uid'];
				break;
			case 'opmerking':
				$this->exemplaren[$exemplaarid]['opmerking']=$value;
				break;
			default:
				throw new Exception('Veld ['.$key.'] is niet toegestaan Boek::setValue()');
		}
	}

	/* 
	 * controleert rechten voor wijderactie
	 * @param	geen of id van een beschrijving
	 * @return	bool
	 * 		boek mag alleen door admins verwijdert worden
	 * 		een beschrijving mag door eigenaar van beschrijving en door admins verwijdert worden.
	 */
	public function magVerwijderen($beschrijvingid=null){
		$uid=LoginLid::instance()->getUid();
		if(Loginlid::instance()->hasPermission('groep:BASFCie','P_BIEB_MOD')){ return true;}
		if($uid=='x999'){ return false;}
		
		//of boekbeschrijving mag verwijderen
		if($beschrijvingid!==null){
			$aBeschrijving=$this->getBeschrijving($beschrijvingid);
			return $aBeschrijving['schrijver_uid'] ==$uid;
		}else{
			//geen rechten om aan te passen
			return false;
		}
	}
	/* 
	 * controleert rechten voor bewerkactie
	 * @param	geen of id van een beschrijving
	 * @return	bool
	 * 		boek mag alleen door admins of door eigenaar v.e. exemplaar bewerkt worden
	 * 		een beschrijving mag door schrijver van beschrijving en door admins bewerkt worden.
	 */
	public function magBewerken($beschrijvingid=null){
		$uid=LoginLid::instance()->getUid();

		//admin of nobodies
		if($this->magVerwijderen() OR Loginlid::instance()->hasPermission('P_BIEB_EDIT')){ return true;}
		if($uid=='x999'){ return false;}

		//of boekbeschrijving mag aanpassen
		if($beschrijvingid!==null){
			$aBeschrijving=$this->getBeschrijving($beschrijvingid);
			return $aBeschrijving['schrijver_uid'] ==$uid;
		}elseif($this->isEigenaar()){
			//is eigenaar van boek
			return true;
		}else{
			//geen rechten om aan te passen
			return false;
		}
	}
	/*
	 * Iedereen met extra rechten en zij met BIEB_READ mogen
	 */
	public function magBekijken(){
		return Loginlid::instance()->hasPermission('P_BIEB_READ') OR $this->magBewerken();
	}
	/*
	 * Controleert of ingelogd eigenaar is van boek/exemplaar
	 *  - Basfcieleden zijn eigenaar van boeken van de bibliotheek
	 *
	 * @param geen of $exemplaarid integer
	 * @return	true
	 * 				of ingelogd eigenaar is v.e. exemplaar van het boek 
	 * 				of van het specifieke exemplaar als exemplaarid is gegeven.
	 * 			false
	 * 				geen geen resultaat of niet de eigenaar
	 */
	public function isEigenaar($exemplaarid=null){
		$db=MySql::instance();
		if($exemplaarid==null){
			$where="WHERE boek_id =".(int)$this->getId();
		}else{
			$where="WHERE id =".(int)$exemplaarid;
		}
		$qEigenaar="
			SELECT eigenaar_uid
			FROM  `biebexemplaar` 
			".$where.";";
		$result=$db->query($qEigenaar);

		$return = false;
		if($db->numRows($result)>0){
			while($eigenaar=$db->next($result)){
				if($eigenaar['eigenaar_uid']==Loginlid::instance()->getUid()){
					$return = true;
				}elseif($eigenaar['eigenaar_uid']=='x222' AND $this->isBASFCie()){
					$return = true;
				}
			}
		}else{
			$this->error.= mysql_error();
		}
		return $return;
	}

	public function isBASFCie(){
		return Loginlid::instance()->hasPermission('groep:BASFCie');
	}
	/*
	 * Check of ingelogd lener is van exemplaar
	 * 
	 * @param $exemplaarid 
	 * @return bool
	 */
	public function isLener($exemplaarid){
		$db=MySql::instance();
		$qLener="
			SELECT uitgeleend_uid 
			FROM `biebexemplaar`
			WHERE id=".(int)$exemplaarid.";";
		$result=$db->query($qLener);
		if($db->numRows($result)>0){
			$lener=$db->next($result);
			return $lener['uitgeleend_uid']==Loginlid::instance()->getUid();
		}else{
			$this->error.= mysql_error();
			return false;
		}
	}
	/*
	 * Slaat het object Boek op in db
	 */
	public function save(){

		$db=MySql::instance();
		$qSave="
			INSERT INTO biebboek (
				titel, auteur, categorie_id, uitgavejaar, uitgeverij, paginas, taal, isbn, code
			) VALUES (
				'".$db->escape($this->getTitel())."',
				'".$db->escape($this->getAuteur())."',
				".(int)$this->getRubriek()->getId().",
				".(int)$this->getUitgavejaar().",
				'".$db->escape($this->getUitgeverij())."',
				".(int)$this->getPaginas().",
				'".$db->escape($this->getTaal())."',
				'".$db->escape($this->getISBN())."',
				'".$db->escape($this->getCode())."'
			);";
		if($db->query($qSave)){
			//id ook opslaan in object Boek.
			$this->id=$db->insert_id();
			if($this->biebboek=='ja'){
				$eigenaar = 'x222';//C.S.R.Bieb is eigenaar
			}else{
				$eigenaar = Loginlid::instance()->getUid();
			}
			return $this->addExemplaar($eigenaar);
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::save()';
		return false;
	}


	/*
	 * Opslaan van waarde van een bewerkbaar veld in db
	 */
	public function saveProperty($entry){
		$db=MySql::instance();
		$key = $entry;//op een enkele uitzondering na
		$table = "biebboek";
		$id = $this->getId();

		//$entry voor leners en opmerkingen eerst opsplitsen
		if(substr($entry,0,6)=='lener_'){
			$exemplaarid = substr($entry,6);
			$entry='lener';
		}elseif(substr($entry,0,10)=='opmerking_'){
			$exemplaarid = substr($entry,10);
			$entry='opmerking';
		}

		switch($entry){
			case 'rubriek':
				$value = (int)$this->getRubriek()->getId();
				$key = "categorie_id";
				break;
			case 'uitgavejaar':
			case 'paginas':
				$value = (int)$this->$entry;
				break;
			case 'titel':
			case 'uitgeverij':
			case 'taal':
			case 'isbn':
			case 'code':
			case 'auteur':
				$value = "'".$db->escape($this->$entry)."'";
				break;
			case 'lener':
				return $this->leenExemplaar($exemplaarid, $this->exemplaren[$exemplaarid]['uitgeleend_uid']);
			case 'opmerking':
				$table = "biebexemplaar";
				$key = "opmerking";
				$value = "'".$db->escape($this->exemplaren[$exemplaarid]['opmerking'])."'";
				$id = (int)$exemplaarid;
				break;
			default:
				$this->error.='Veld ['.$entry.'] is niet toegestaan Boek::saveProperty()';
				return false;
		}

		$qSave="
			UPDATE ".$table." SET
				".$key."= ".$value."
			WHERE id= ".$id."
			LIMIT 1;";
		if($db->query($qSave)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::saveProperty()';
		return false;
	}
	/*
	 * Verwijder een boek
	 */
	public function delete(){
		if($this->getId()==0){
			$this->error.='Kan geen lege boek met id=0 wegkekken. Boek::delete()';
			return false;
		}
		$db=MySql::instance();
		$qDeleteBeschrijvingen="DELETE FROM biebbeschrijving WHERE boek_id=".$this->getId().";";
		$qDeleteExemplaren="DELETE FROM biebexemplaar WHERE boek_id=".$this->getId()." LIMIT 1;";
		$qDeleteBoek="DELETE FROM biebboek WHERE id=".$this->getId()." LIMIT 1;";
		return $db->query($qDeleteBeschrijvingen) AND $db->query($qDeleteExemplaren) AND $db->query($qDeleteBoek);
	}


	/********************************
	 * Boekrecensies/beschrijvingen *
	 ********************************
	 * 
	 * laad beschrijvingen van dit boek
	 * @return void
	 */
	public function loadBeschrijvingen(){
		$db=MySql::instance();
		$query="
			SELECT id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
			FROM biebbeschrijving
			WHERE boek_id=".(int)$this->getId()."
			ORDER BY toegevoegd;";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			while($beschrijving=$db->next($result)){
				$this->beschrijvingen[]=$beschrijving;
			}
		}else{
			$this->error .= mysql_error();
			return false;
		}
		return $db->numRows($result);
	}

	/*
	 * Geeft beschrijvingen van dit boek
	 * @return array met beschrijvingen
	 */
	public function getBeschrijvingen(){
		if($this->beschrijvingen===null){
			$this->loadBeschrijvingen();
		}
		return $this->beschrijvingen; 
	}
	/*
	 * Aantal beschrijvingen
	 * @return int aantal beschrijvingen
	 */
	public function countBeschrijvingen(){
		if($this->beschrijvingen===null){
			$this->loadBeschrijvingen();
		}
		return count($this->beschrijvingen);
	}
	/*
	 * Geeft beschrijving terug
	 * @param
	 * 		geen: haalt waarde uit object Boek
	 * 		$beschrijvingid:  van beschrijving uit db halen
	 * @return string
	 */
	public function getBeschrijving($beschrijvingid=null){
		if($beschrijvingid===null){
			return $this->beschrijving;
		}else{
			$db=MySql::instance();
			$query="
				SELECT id, boek_id, schrijver_uid, beschrijving, toegevoegd, bewerkdatum
				FROM biebbeschrijving
				WHERE id=".(int)$beschrijvingid."
				LIMIT 1;";
			$result=$db->query($query);
			
			if($db->numRows($result)>0){
				$beschrijving = $db->next($result);
				return $beschrijving;
			}else{
				$this->error .= mysql_error();
				return 'Mislukt. Boek::getBeschrijving()';
			}
		}
	}
	/*
	 * Geeft $beschrijvingsid
	 */
	public function getBeschrijvingsId(){
		return $this->beschrijvingsid;
	}

	/*
	 * Sla boekrecensie/beschrijving op
	 */
	public function saveBeschrijving($bewerken=false){
		$db=MySql::instance();
		if($bewerken==false){
			$qSave="
				INSERT INTO biebbeschrijving (
					boek_id, schrijver_uid, beschrijving, toegevoegd
				) VALUES (
					".(int)$this->getId().",
					'".$db->escape(Loginlid::instance()->getUid())."',
					'".$db->escape($this->getBeschrijving())."',
					'".getDateTime()."'
				);";
		}else{
			$qSave="
				UPDATE biebbeschrijving SET
					beschrijving= '".$db->escape($this->getBeschrijving())."',
					bewerkdatum='".getDateTime()."'
				WHERE id= ".$this->getBeschrijvingsId()."
				LIMIT 1;";
		}
		if($db->query($qSave)){
			$this->beschrijvingid=$db->insert_id();//id van beschrijving weer tijdelijk opslaan, zodat we beschrijving kunnen linken
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::saveBeschrijving()';
		return false;
	}

	/*
	 * verwijder beschrijving
	 * 
	 * @param $beschrijvingsid
	 * @return	true geslaagd
	 * 			false mislukt, iig id=0 is false
	 */
	public function verwijderBeschrijving($beschrijvingsid){
		if($beschrijvingsid==0){
			$this->error.='Beschrijving 0 bestaat niet. Boek::verwijderBeschrijving()';
			return false;
		}
		$db=MySql::instance();
	 	$qVerwijderBeschrijving="DELETE FROM biebbeschrijving WHERE id=".(int)$beschrijvingsid." LIMIT 1;";
		return $db->query($qVerwijderBeschrijving);
	}

	/**************
	 * Exemplaren *
	 **************
	 * 
	 * voeg exemplaar toe
	 * @param $eigenaar
	 * @return  true geslaagd
	 * 			false 	mislukt
	 * 					$eigenaar is ongeldig uid
	 */
	public function addExemplaar($eigenaar){
		if(!Lid::isValidUid($eigenaar)){
			return false;
		}
		$db=MySql::instance();
		$qSave="
			INSERT INTO biebexemplaar (
				boek_id, eigenaar_uid, toegevoegd, status
			) VALUES (
				".(int)$this->getId().",
				'".$db->escape($eigenaar)."',
				'".getDateTime()."',
				'beschikbaar'
			);";
		if($db->query($qSave)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::addExemplaar()';
		return false;
	}
	/*
	 * verwijder exemplaar
	 * @param $id exemplaarid
	 * @return 	true geslaagd
	 * 			false mislukt
	 */
	public function verwijderExemplaar($id){
		$db=MySql::instance();
		$qDeleteExemplaar="DELETE FROM biebexemplaar WHERE id=".(int)$id." LIMIT 1;";
		return $db->query($qDeleteExemplaar);
	}
	/*
	 * 

	 * 
	 * laad exemplaren van dit boek in Boek
	 * @return void
	 */
	public function loadExemplaren(){
		$db=MySql::instance();
		$query="
			SELECT id, eigenaar_uid, opmerking, uitgeleend_uid, toegevoegd, status, uitleendatum
			FROM biebexemplaar
			WHERE boek_id=".(int)$this->getId()."
			ORDER BY toegevoegd;";
		$result=$db->query($query);
		
		if($db->numRows($result)>0){
			while($exemplaar=$db->next($result)){
				$this->exemplaren[$exemplaar['id']]=$exemplaar;
			}
		}else{
			$this->error .= mysql_error();
			return false;
		}
		return $db->numRows($result);
	}
	/*
	 * Geeft alle exemplaren van dit boek
	 * @return array met exemplaren
	 */
	public function getExemplaren(){
		if($this->exemplaren===null){
			$this->loadExemplaren();
		}
		return $this->exemplaren; 
	}
	/*
	 * Aantal exemplaren
	 * @return int
	 */
	public function countExemplaren(){
		if($this->exemplaren===null){
			$this->loadExemplaren();
		}
		return count($this->exemplaren);
	}
	/*
	 * Geeft status van exemplaar
	 * 
	 * @param $exemplaarid int 
	 * @return 	statuswaarde uit db van $exemplaarid
	 * 			of anders lege string
	 */
	public function getStatusExemplaar($exemplaarid){
		$db=MySql::instance();
		$query="
			SELECT id, status
			FROM biebexemplaar
			WHERE id=".(int)$exemplaarid.";";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			$exemplaar=$db->next($result);
			return $exemplaar['status'];
		}else{
			$this->error.= mysql_error();
			return '';
		}
	}
	/*
	 * slaat op dat een exemplaar is geleend
	 * 
	 * @param $exemplaarid wordt status 'uitgeleend' in db
	 * @return	true geslaagd
	 * 			false mislukt
	 */
	public function leenExemplaar($exemplaarid,$lener=null){
		//alleen status beschikbaar toegestaan, of je moet eigenaar zijn die iemand toevoegd (tbv editable fields)
		if($this->getStatusExemplaar($exemplaarid)!='beschikbaar' ){
			$this->error.='Boek is niet beschikbaar. leenExemplaar()';
			return false;
		}
		if($lener==null){
			$lener=Loginlid::instance()->getUid();
		}

		$db=MySql::instance();
		$query="
			UPDATE biebexemplaar SET
				uitgeleend_uid = '".$db->escape($lener)."',
				status = 'uitgeleend',
				uitleendatum = '".getDateTime()."',
				leningen=leningen +1
			WHERE id = ".(int)$exemplaarid."
			LIMIT 1;";
		if($db->query($query)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::leenExemplaar()';
		return false;
	}
	/*
	 * slaat op dat een exemplaar iemand exemplaar teruggeeft
	 * 
	 * @param $exemplaarid wordt status 'terugegeven' in db
	 * @return	true geslaagd
	 * 			false mislukt
	 */
	public function teruggevenExemplaar($exemplaarid){
		if($this->getStatusExemplaar($exemplaarid)!='uitgeleend'){
			$this->error.='Boek is niet uitgeleend. ';
			return false;
		}

		$db=MySql::instance();
		$query="
			UPDATE biebexemplaar SET
				status = 'teruggegeven'
			WHERE id = ".(int)$exemplaarid."
			LIMIT 1;";
		if($db->query($query)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::teruggegevenExemplaar()';
		return false;
	}
	/*
	 * slaat op dat een exemplaar iemand exemplaar heeft ontvangen
	 * 
	 * @param $exemplaarid wordt status 'beschikbaar' in db
	 * @return	true geslaagd
	 * 			false mislukt
	 */
	public function terugontvangenExemplaar($exemplaarid){
		if(!in_array($this->getStatusExemplaar($exemplaarid), array('uitgeleend', 'teruggegeven'))){
			$this->error.='Boek is niet uitgeleend. ';
			return false;
		}
		$db=MySql::instance();
		$query="
			UPDATE biebexemplaar SET
				uitgeleend_uid = '',
				status = 'beschikbaar'
			WHERE id = ".(int)$exemplaarid."
			LIMIT 1;";
		if($db->query($query)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::terugontvangenExemplaar()';
		return false;
	}
	/*
	 * markeert exemplaar als vermist
	 * 
	 * @param $exemplaarid wordt status 'vermist' in db
	 * @return	true gelukt
	 * 			false mislukt
	 */
	public function vermistExemplaar($exemplaarid){
		if($this->getStatusExemplaar($exemplaarid)=='vermist'){
			$this->error.='Boek is al vermist. ';
			return false;
		}elseif($this->getStatusExemplaar($exemplaarid)!='beschikbaar'){
			$this->error.='Boek is nog uitgeleend. ';
			return false;
		}

		$db=MySql::instance();
		$query="
			UPDATE biebexemplaar SET
				status = 'vermist',
				uitleendatum = '".getDateTime()."'
			WHERE id = ".(int)$exemplaarid."
			LIMIT 1;";
		if($db->query($query)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::vermistExemplaar()';
		return false;
	}
	/*
	 * markeert exemplaar als beschikbaar
	 * 
	 * @param $exemplaarid wordt status 'beschikbaar' in db
	 * @return	true gelukt
	 * 			false mislukt
	 */
	public function gevondenExemplaar($exemplaarid){
		if($this->getStatusExemplaar($exemplaarid)!='vermist'){
			$this->error.='Boek is niet vermist gemeld. ';
			return false;
		}

		$db=MySql::instance();
		$query="
			UPDATE biebexemplaar SET
				status = 'beschikbaar'
			WHERE id = ".(int)$exemplaarid."
			LIMIT 1;";
		if($db->query($query)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Boek::gevondenExemplaar()';
		return false;
	}



	/******************************************************************************
	 * methodes voor gewone formulieren *
	 ******************************************************************************/

	/*
	 * Definiëren van de velden van het nieuw boek formulier
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het boekaddding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	protected function getCommonFields($naamtitelveld='Titel'){
		$fields['titel']=new RequiredAutoresizeTextField('titel', $this->getTitel(), $naamtitelveld, 200, 'Titel ontbreekt!');
		$fields['auteur']=new InputField('auteur', $this->getAuteur(), 'Auteur', 100);
		$fields['auteur']->setRemoteSuggestionsSource("/communicatie/bibliotheek/autocomplete/auteur");
		$fields['auteur']->setPlaceholder('Achternaam, Voornaam V.L. van de');
		$fields['paginas']=new IntField('paginas', $this->getPaginas() , "Pagina's", 10000, 0);
		$fields['taal']=new InputField('taal', $this->getTaal(), 'Taal', 25);
		$fields['taal']->setRemoteSuggestionsSource("/communicatie/bibliotheek/autocomplete/taal");
		$fields['isbn']=new InputField('isbn', $this->getISBN(), 'ISBN',15);
		$fields['isbn']->setPlaceholder('Uniek nummer');
		$fields['uitgeverij']=new InputField('uitgeverij', $this->getUitgeverij(), 'Uitgeverij', 100);
		$fields['uitgeverij']->setRemoteSuggestionsSource("/communicatie/bibliotheek/autocomplete/uitgeverij");
		$fields['uitgavejaar']=new IntField('uitgavejaar', $this->getUitgavejaar(), 'Uitgavejaar', 2100, 0);
		$fields['rubriek']=new SelectField('rubriek', $this->getRubriek()->getId(), 'Rubriek', Rubriek::getAllRubrieken($samenvoegen=true,$short=true));
		$fields['code']=new InputField('code', $this->getCode(), 'Biebcode', 7);
		return $fields;
	}
	public function assignFieldsNieuwboekForm(){
		//Iedereen die bieb mag bekijken mag nieuwe boeken toevoegen
		if($this->magBekijken()){
			$nieuwboekform['boekgeg']=new Comment('Boekgegevens:');
			$nieuwboekform=$nieuwboekform+$this->getCommonFields();
			if($this->isBASFCie()){
				$nieuwboekform['biebboek']=new SelectField('biebboek', $this->biebboek, 'Is een biebboek?', array('ja'=>'C.S.R. boek', 'nee'=>'Eigen boek'));
			}
			$nieuwboekform['submit']=new SubmitButton('opslaan', '<a class="knop" href="/communicatie/bibliotheek/">Annuleren</a>');

			$this->nieuwboekform=new Formulier('/communicatie/bibliotheek/nieuwboek/0', $nieuwboekform);
			$this->nieuwboekform->cssID='boekaddForm';
		}
	}
	/* 
	 * maakt objecten van formulier om beschrijving toe te voegen
	 */
	public function assignFieldsBeschrijvingForm($bewerken=false){
		if($this->magBekijken()){
			if($bewerken){
				$boekbeschrijvingform[]=new Comment('Bewerk uw beschrijving of recensie van het boek:');
			}else{
				$boekbeschrijvingform[]=new Comment('Geef uw beschrijving of recensie van het boek:');
			}
				$textfield=new RequiredPreviewTextField('beschrijving', $this->getBeschrijving(), '.');
				$textfield->previewOnEnter();
			$boekbeschrijvingform[]=$textfield;
			$boekbeschrijvingform[]=new SubmitButton();

			$posturl='/communicatie/bibliotheek/';
			if($bewerken){
				$posturl.='bewerkbeschrijving/'.$this->getId().'/'.$this->getBeschrijvingsId();
			}else{
				$posturl.='addbeschrijving/'.$this->getId();
			}
			$this->boekbeschrijvingform=new Formulier($posturl, $boekbeschrijvingform);
			$this->boekbeschrijvingform->cssID='addBeschrijving';
		}
	}
	/*
	 * maakt objecten voor de bewerkbare velden van een boek
	 * 
	 */
	public function assignAjaxFieldsForm(){
		$editablefieldsform=array();
		//Eigenaar een exemplaar v.h. boek mag alleen bewerken
		if($this->isEigenaar()){
			$editablefieldsform=$this->getCommonFields('Boek');
		}

		//voor eigenaars een veldje maken om boek uit te lenen.
		if($this->exemplaren===null){
			$this->loadExemplaren();
		}
		if(count($this->exemplaren)>0){
			foreach($this->exemplaren as $exemplaar){//id, eigenaar_uid, uitgeleend_uid, toegevoegd, status, uitleendatum
				if($this->isEigenaar($exemplaar['id'])){
					$editablefieldsform['lener_'.$exemplaar['id']]=new LidField('lener_'.$exemplaar['id'], $exemplaar['uitgeleend_uid'], 'Uitgeleend aan');//, Catalogus::getAllValuesOfProperty('naam'), 'Geef naam of lidnummer van lener');
					$editablefieldsform['opmerking_'.$exemplaar['id']]=new AutoresizeTextField('opmerking_'.$exemplaar['id'], $exemplaar['opmerking'], 'Opmerking', 255, 'Geef opmerking over exemplaar..');
				}
			}
		}
		$this->editablefieldsform=new Formulier('', $editablefieldsform);
	}
	/*
	 * Geeft formulier terug
	 */
	public function getFormulier($form){
		switch($form){
			case 'nieuwboek':
				return $this->nieuwboekform;
			case 'beschrijving':
				return $this->boekbeschrijvingform;
			case 'bewerkboek':
				return $this->editablefieldsform;
		}
		return null;
	}
	/*
	 * Geeft één veldobject $entry terug
	 */
	public function getField($entry){ 
		return $this->editablefieldsform->findByName($entry);
	}

	/**
	 * Controleren of alle velden van formulier correct zijn
	 */
	public function validForm($form){
		return $this->getFormulier($form)->valid();
	}
	/*
	 * Controleren of het gevraagde veld $entry correct is
	 */
	public function validField($entry){
		//we checken alleen de formfields, niet de comments enzo.
		$field = $this->getField($entry);
		return $field instanceof FormField AND $field->valid();
	}

	/*
	 * Slaat alle velden van formulier op
	 * @param $form string formuliernaam
	 * @param $bewerken bool Alleen voor formulier 'beschrijving'. true=bewerken,false=nieuw.
	 * @return bool gelukt/mislukt
	 */
	public function saveForm($form, $bewerken=false){
		//object Boek vullen
		foreach($this->getFormulier($form)->getFields() as $field){
			if($field instanceof FormField){
				$this->setValue($field->getName(), $field->getValue());
			}
		}
		//object Boek opslaan
		if($form=='nieuwboek'){
			if($this->save()){
				return true;
			}
		//of de beschrijving/recensie opslaan
		}elseif($form=='beschrijving'){
			if($this->saveBeschrijving($bewerken)){
				return true;
			}
		}
		return false;
	}
	/*
	 * Slaat één veld $entry op in db
	 */
	public function saveField($entry){
		//waarde van $entry in Boek invullen
		$field = $this->getField($entry);
		if($field instanceof FormField){
			$this->setValue($field->getName(), $field->getValue());
		}else{
			$this->error .= 'saveField(): '.$entry.' Geen instanceof FormField';
			return false;
		}
		//waarde van $entry uit Boek opslaan
		if($this->saveProperty($entry)){
			return true;
		}else{
			$this->error .= 'saveField(): saveProperty mislukt. ';
		}
		return false;
	}

}
?>
