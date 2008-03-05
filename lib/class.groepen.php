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
 * Deze klasse is een verzameling van groepen van een bepaald type.
 */
 
class Groepen{
	
	private $type;
	
	private $groepen=array();
	
	/*
	 * Constructor voor Groepen.
	 * 
	 * @param	$groeptype		Welke groepen moeten geladen worden?
	 * @return 	void
	 */
	public function __construct($groeptype){
		$db=MySql::get_MySql();
		
		//we laden eerst de gegevens over de groep op
		$qGroeptype="
			SELECT id, naam, beschrijving
			FROM groeptype
			WHERE groeptype.naam='".$db->escape($groeptype)."'
			LIMIT 1;";
		$rGroeptype=$db->query($qGroeptype);
		if($rGroeptype!==false AND $db->numRows($rGroeptype)==1){
			$this->type=$db->next($rGroeptype);
		}else{
			die('FATALE FEUT: Groeptype bestaat niet! Groepen::load()');
		}

		//Vervolgens de groepen van het gegeven type ophalen:
		$this->load();
	}
	
	/*
	 * Laten we de gegevens van het groeptype ophalen, met de bekende groepen voor
	 * het type.
	 */
	private function load(){
		$db=MySql::get_MySql();
			
		$qGroepen="
			SELECT 
				groep.id AS groepId, groep.snaam AS snaam, groep.naam AS naam,
				groep.sbeschrijving AS sbeschrijving, groep.beschrijving AS beschrijving, groep.zichtbaar AS zichtbaar,
				groeplid.uid AS uid, groeplid.op AS op, groeplid.functie AS functie, groeplid.prioriteit AS prioriteit 
			FROM groep
			LEFT JOIN groeplid ON(groep.id=groeplid.groepid) 
			WHERE groep.gtype=".$this->getId()."
			  AND groep.zichtbaar='zichtbaar'
			ORDER BY groep.snaam ASC, groeplid.prioriteit ASC;";
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
		//tot slot de laatste groep ook toevoegen
		$this->groepen[$aGroep[0]['groepId']]=new Groep($aGroep);
		
	}
	/*
	 * Sla de huidige toestand van de groep op in de database.
	 */
	public function save(){
		$db=MySql::get_MySql();
		$qSave="
			UPDATE groeptype SET 
				naam='".$db->escape($this->getType())."',
				beschrijving='".$db->escape($db->getBeschrijving())."'
			WHERE id=".$this->getId()."
			LIMIT 1;";
		return $db->save();
	}
	
	public function getId(){		return $this->type['id']; }
	public function getType(){ 			return $this->type['naam']; }
	public function getBeschrijving(){	return $this->type['beschrijving']; }
	
	public function getGroep($groepId){
		if(isset($this->groepen[$groepId])){
			return $this->groepen[$groepId];
		}
		return false;
	}
	/*
	 * statische functie om de groepen bij een gebruiker te zoeken.
	 * 
	 * @param	$uid	Gebruiker waarvoor groepen moeten worden opgezocht
	 * @return			Array met groepen
	 */
	public static function getGroepenByUid($uid){
		$db=MySql::get_MySql();
		$groepen=array();
		$result=$db->query("
			SELECT id, snaam, naam
			FROM groep
			WHERE id IN ( 
				SELECT groepid FROM groeplid WHERE uid = '".$db->escape($uid)."'
			)
			ORDER BY naam;");
		if ($result !== false and $db->numRows($result) > 0){
			$groepen=$db->result2array($result);
		}
		return $groepen;
	}
}

?>
