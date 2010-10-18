<?php
/*
 * class.groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Groepen zijn als volgt in de db opgeslagen:
 * groeptype:	Verschillende 'soorten' groepen: commissies, woonoorden, etc.
 * groep:		De daadwerkelijke groepen.
 * groeplid:	De leden van verschilllende groepen.
 *
 * leden kunnen uiteraard lid zijn van verschillende groepen, maar niet meer
 * dan één keer in een bepaalde groep zitten.
 *
 * Deze klasse is een verzameling van groepobjecten van een bepaald type. Standaard
 * worden alleen de h.t.-groepen opgehaald.
 */
require_once 'groep.class.php';

class Groepen{

	private $type;

	private $groepen=null;

	/*
	 * Constructor voor Groepen.
	 *
	 * @param	$groeptype		Welke groepen moeten geladen worden?
	 * @return 	void
	 */
	public function __construct($groeptype){
		$db=MySql::instance();

		if(is_int($groeptype)){
			$where="groeptype.id=".(int)$groeptype;
		}else{
			$where="groeptype.naam='".$db->escape($groeptype)."'";
		}
	
		//we laden eerst de gegevens over de groep op
		$query="
			SELECT id, naam, beschrijving, toonHistorie FROM groeptype
			WHERE ".$where." LIMIT 1;";
		$categorie=$db->getRow($query);
		if(is_array($categorie)){
			$this->type=$categorie;
		}else{
			throw new Exception('Groeptype ('.$groeptype.') bestaat niet! Groepen::__construct()');
		}
	}

	/*
	 * De gevens van het groeptype ophalen, met de bekende groepen voor
	 * het type.
	 */
	private function loadGroepen(){
		$db=MySql::instance();

		//Afhankelijk van de instelling voor het groeptype halen we alleen de
		//h.t.-groepen op, of ook de o.t.-groepen.
		$htotFilter="groep.status='ht'";
		$sort='';
		if($this->getToonHistorie()){
			$htotFilter.=" OR groep.status='ot'";
			$sort="groep.begin DESC, groep.id ASC, ";
		}

		$qGroepen="
			SELECT
				groep.id AS groepId, groep.gtype as gtypeId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groep.status AS status, begin, einde, aanmeldbaar, functiefilter, limiet, toonFuncties, toonPasfotos, lidIsMod,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid)
			LEFT JOIN lid ON(groeplid.uid=lid.uid)
			WHERE groep.gtype=".$this->getId()."
			  AND groep.zichtbaar='zichtbaar'
			  AND (".$htotFilter.")
			ORDER BY ".$sort." groep.snaam ASC, groeplid.prioriteit ASC, lid.achternaam ASC, lid.voornaam;";
		$rGroepen=$db->query($qGroepen);
		//nu een beetje magic om een stapeltje groepobjecten te genereren:
		$currentGroepId=null;
		$aGroep=array();
		while($aGroepraw=$db->next($rGroepen)){
			//eerste groepid in de huidige groep stoppen
			if($currentGroepId==null){ $currentGroepId=$aGroepraw['groepId']; }

			//zijn we bij een volgende groep aangekomen?
			if($currentGroepId!=$aGroepraw['groepId']){
				//groepobject maken en aan de array toevoegen

				$this->groepen[$aGroep[0]['groepId']]=new Groep($aGroep);

				//tenslotte nieuwe groep als huidige kiezen en groeparray leegmikken
				$currentGroepId=$aGroepraw['groepId'];
				$aGroep=array();

			}
			$aGroep[]=$aGroepraw;
		}

		if(isset($aGroep[0])){
			//tot slot de laatste groep ook toevoegen
			$this->groepen[$aGroep[0]['groepId']]=new Groep($aGroep);
		}
	}
	/*
	 * Sla de huidige toestand van het groeptype op in de database.
	 * LET OP: deze methode doet niets met de ingeladen groepen.
	 */
	public function save(){
		$db=MySql::instance();
		$qSave="
			UPDATE groeptype
			SET beschrijving='".$db->escape($this->getBeschrijving())."'
			WHERE id=".$this->getId()."
			LIMIT 1;";
		return $db->query($qSave);
	}

	public function getGroepen(){
		if($this->groepen===null){
			$this->loadGroepen();
		}
		return $this->groepen;
	}
	public function getId(){			return $this->type['id']; }
	public function getNaam(){ 			return $this->type['naam']; }
	public function getBeschrijving(){	return $this->type['beschrijving']; }
	public function getToonHistorie(){	return $this->type['toonHistorie']==1; }

	public static function isAdmin(){
		return LoginLid::instance()->hasPermission('P_LEDEN_MOD');
	}

	public function getGroep($groepId){
		if($this->groepen===null){
			$this->loadGroepen();
		}
		if(isset($this->groepen[$groepId])){
			return $this->groepen[$groepId];
		}
		return false;
	}

	/*
	 * statische functie om de groepen bij een gebruiker te zoeken.
	 *
	 * @param	$uid	Gebruiker waarvoor groepen moeten worden opgezocht
	 * @return			Array met Groep-objectjes
	 */
	public static function getByUid($uid){
		$db=MySql::instance();

		$groepen=array();
		if(Lid::isValidUid($uid)){
			$qGroepen="
				SELECT
					groep.id AS id
				FROM groep
				INNER JOIN groeptype ON(groep.gtype=groeptype.id)
				WHERE groeptype.toonProfiel=1
				  AND groep.id IN (
					SELECT groepid FROM groeplid WHERE uid = '".$uid."'
				)
				ORDER BY groep.status, groeptype.prioriteit, groep.naam;";

			$rGroepen=$db->query($qGroepen);
			if ($rGroepen !== false and $db->numRows($rGroepen) > 0){
				while($row=$db->next($rGroepen)){
					$groepen[]=new Groep($row['id']);
				}
			}
		}
		return $groepen;
	}
	//Alle h.t. groepen in een categorie o.t. maken.
	public function maakGroepenOt(){
		$error='';
		if($this->groepen===null){
			$this->loadGroepen();
		}
		if(count($this->groepen)==0){
			return true;
		}
		foreach($this->groepen as $groep){
			if(!$groep->maakOt()){
				$error.='';
			}
		}
		return $error=='';
	}

	
	/*
	 * Haal de huidige groepen van een bebaald type voor een bepaald lid.
	 */
	public static function getByTypeAndUid($type, $uid){
		$db=MySql::instance();

		$groepen=array();
		if(Lid::isValidUid($uid)){
			$qGroepen="
				SELECT id
				FROM groep
				WHERE gtype IN (
					SELECT id
					FROM groeptype
					WHERE id=".(int)$type."
				) AND id IN (
					SELECT groepid FROM groeplid WHERE uid = '".$uid."'
				);";
			$rGroepen=$db->query($qGroepen);
			if ($rGroepen !== false and $db->numRows($rGroepen) > 0){
				while($row=$db->next($rGroepen)){
					$groepen[]=new Groep($row['id']);
				}
			}
		}
		return $groepen;
	}
	
	/*
	 * Statische functie om een verzameling van groeptypes terug te geven
	 *
	 * @return		Array met groeptypes
	 */
	public static function getGroeptypes($alleenZichtbaar=true){
		$db=MySql::instance();
		$qGroeptypen="
			SELECT id, naam
			FROM groeptype ";
		if($alleenZichtbaar===true){ $qGroeptypen.="WHERE zichtbaar=1 "; }
		$qGroeptypen.="ORDER BY prioriteit ASC, naam ASC;";
		$rGroeptypen=$db->query($qGroeptypen);
		return $db->result2array($rGroeptypen);
	}

	public static function isValidGtype($gtypetotest){
		$db=MySql::instance();
		$qGroep="SELECT id FROM groeptype WHERE naam='".$db->escape($gtypetotest)."'";
		return $db->numRows($db->query($qGroep))==1;
	}

	/*
	 * Statische functie die de werkgroepleiders teruggeeft
	 *
	 * @return		Array met uid van werkgroepleiders
	 */
	public static function getWerkgroepLeiders(){
		$db=MySql::instance();
		$Werkgroepleiders = "
			SELECT uid
			FROM groeplid
			WHERE (functie='Leider' OR functie='leider')
			AND groepid IN (
				SELECT groep.id
				FROM groep JOIN groeptype ON groep.gtype = groeptype.id
				WHERE groeptype.naam='Werkgroepen' AND groep.status='ht')";
		$result = $db->result2array($db->query($Werkgroepleiders));
		$leiders = array();
		if(is_array($result)){
			foreach($result as $leider) {
				array_push($leiders, $leider['uid']);
			}
		}
		return $leiders;
	}
}

?>
