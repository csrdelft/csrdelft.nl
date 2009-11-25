<?php
/*
 * class.mededeling.php	|  Maarten Somhorst
 *
 *
 */

require_once('class.mededelingcategorie.php');

class Mededeling{

	private $id=0;
	private $datum;
	private $uid;
	private $titel;
	private $tekst;
	private $zichtbaarheid;
	private $prive=0;
	private $categorieId=0;
	private $prioriteit;
	private $plaatje='';

	private $categorie=null;

	const defaultPrioriteit=255;

	public function __construct($init){
		if(is_array($init)){
			if($init['id']>0)
				$this->load($init['id']);
			$this->array2properties($init);
		}else{
			$init=(int)$init;
			if($init!=0){
				$this->load($init);
			}else{
				//default waarden voor een nieuwe mededeling
				$this->datum=getDateTime();
				$this->uid=LoginLid::instance()->getUid();
				$this->prioriteit=self::defaultPrioriteit;
			}
		}
	}
	public function load($id=0){
		$db=MySql::instance();
		$loadQuery="
			SELECT id, datum, titel, tekst, categorie, uid, prioriteit, prive, zichtbaarheid, plaatje, categorie
			FROM mededeling
			WHERE id=".(int)$id.";";
		$mededeling=$db->getRow($loadQuery);
		if(!is_array($mededeling)){
			throw new Exception('Mededeling bestaat niet. (Mededeling::load())');
		}
		$this->array2properties($mededeling);
	}
	public function save(){
		$db=MySql::instance();
		if($this->getPrioriteit()!=self::defaultPrioriteit)
		{
			// Eerst even de prioriteit 'resetten'.
			$prioriteitQuery="
				UPDATE mededeling
				SET prioriteit=".self::defaultPrioriteit."
				WHERE prioriteit=".(int)$this->getPrioriteit();
			$db->query($prioriteitQuery);
		}
		if($this->getId()==0){
			$saveQuery="
				INSERT INTO mededeling (
					titel, tekst, datum, uid, prioriteit, prive, zichtbaarheid, categorie, plaatje
				)VALUES(
					'".$db->escape($this->getTitel())."',
					'".$db->escape($this->getTekst())."',
					'".$this->getDatum()."',
					'".$this->getUid()."',
					".(int)$this->getPrioriteit().",
					'".(int)$this->getPrive()."',
					'".$this->getZichtbaarheid()."',
					".(int)$this->getCategorieId().",
					'".$db->escape($this->getPlaatje())."'
				);";
		}else{
			// Alleen als er een nieuw plaatje is hoeft het plaatjesveld geüpdate te worden.
			// TODO: het oude plaatje verwijderen! 
			$setPlaatje='';
			if($this->getPlaatje()!=''){
				$setPlaatje=",
					plaatje='".$db->escape($this->getPlaatje())."'";
			}
			$saveQuery="
				UPDATE
					mededeling
				SET
					titel='".$db->escape($this->getTitel())."',
					tekst='".$db->escape($this->getTekst())."',
					datum='".$this->getDatum()."',
					uid='".$this->getUid()."',
					prioriteit=".(int)$this->getPrioriteit().",
					prive='".(int)$this->getPrive()."',
					zichtbaarheid='".$this->getZichtbaarheid()."',
					categorie=".(int)$this->getCategorieId().
					$setPlaatje."
				WHERE
					id=".$this->getId()."
				LIMIT 1;";
		}
		$queryResult=$db->query($saveQuery);

		$return=-1;
		if($queryResult){
			$return=$this->getId();
			if($return==0){
				$return=$db->insert_id();
			}
		}
		return $return;
	}
	public function keurGoed(){
		$this->zichtbaarheid='zichtbaar';
		$this->save();
	}
	/*
	 * Vult de attributen van dit object met de waarden in de gegeven array.
	 */
	private function array2properties($array){
		$this->id=$array['id'];
		$this->titel=$array['titel'];
		$this->tekst=$array['tekst'];
		if($this->getDatum()===null){ // Als we al een datum hebben (uit de DB), hoeven we het niet te vervangen.
			$this->datum=$array['datum'];
		}
		if($this->getUid()===null){ // Als we al een Uid hebben (uit de DB), hoeven we deze niet te vervangen.
			$this->uid=$array['uid'];
		}
		$this->prioriteit=$array['prioriteit'];
		$this->prive=$array['prive'];
		// Om zichtbaarheid te veranderen moet je moderator zijn en als deze mededeling op goedkeuring wachtte
		// of al verwijderd was, verandert hier niets aan.
		if( $this->getZichtbaarheid()===null OR (Mededeling::isModerator() AND $this->getZichtbaarheid()!='wacht_goedkeuring' AND $this->getZichtbaarheid()!='verwijderd') ) {
			$this->zichtbaarheid=$array['zichtbaarheid'];
		}
		$this->plaatje=$array['plaatje'];
		
		$this->categorieId=$array['categorie'];
	}
	public function getId(){ return $this->id; }
	public function getTitel(){ return $this->titel; }
	public function getAfgeknipteTitel(){
		return Mededeling::knipTekst(mb_htmlentities($this->getTitel(), 34, 1)); //TODO: constanten van maken.
	}
	public function getTekst(){ return $this->tekst; }
	public function getAfgeknipteTekst(){
		return Mededeling::knipTekst(CsrUBB::instance()->getHTML($this->getTekst()), 46, 4); //TODO: constanten van maken.
	}
	public function getDatum(){ return $this->datum; } //TODO: leesbare datum teruggeven(??)
	public function getUid(){ return $this->uid; }
	public function getPrioriteit(){ return $this->prioriteit; }
	public function getPrive(){ return $this->prive; }
	public function isPrive(){ return $this->getPrive()==1; }
	public function getZichtbaarheid(){ return $this->zichtbaarheid; }
	public function isVerborgen(){ return $this->getZichtbaarheid()=='onzichtbaar'; }
	public function getPlaatje(){ return $this->plaatje; }
	public function getCategorieId(){ return $this->categorieId; }
	public function getCategorie($force=false){
		if($force OR $this->categorie===null){
			$this->categorie=new MededelingCategorie($this->getCategorieId());
		}
		return $this->categorie;
	}

	public static function getTopmost($aantal){
		$topmost=array();
		if(is_numeric($aantal) and $aantal>0){
			$db=MySql::instance();
			$priveClause="";
			if( !LoginLid::instance()->hasPermission('P_LEDEN_READ') )
				$priveClause=" AND prive='0'";
			$topmostQuery="
				SELECT id
				FROM mededeling
				WHERE zichtbaarheid='zichtbaar'".$priveClause."
				ORDER BY prioriteit ASC, datum DESC
				LIMIT ".$aantal;
			$resource=$db->select($topmostQuery);
			while( $mededeling=$db->next($resource) ){
				$topmost[]=new Mededeling($mededeling['id']);
			}
		}
		return $topmost;
	}

	public static function getLijstVanPagina($pagina=1, $aantal){
		$mededelingen=array();
		$db=MySql::instance();
		$priveClause=$verborgenClause="";
		$verborgenClause="zichtbaarheid='zichtbaar'";
		if( Mededeling::isModerator() )
			$verborgenClause="zichtbaarheid!='verwijderd'";
		if( !LoginLid::instance()->hasPermission('P_LEDEN_READ') )
			$priveClause=" AND prive='0'";
		$paginaQuery="
			SELECT id, datum
			FROM mededeling
			WHERE ".$verborgenClause.$priveClause."
			ORDER BY datum DESC
			LIMIT ".(($pagina-1)*$aantal).", ".$aantal;
		$resource=$db->select($paginaQuery);
		while( $mededeling=$db->next($resource) )
		{
			$datum=date_create($mededeling['datum']);
			$groepeerstring=$datum->format('F Y'); // Maand voluit en jaar.
			if(!isset($mededelingen[$groepeerstring]))
				$mededelingen[$groepeerstring]=array();
			$mededelingen[$groepeerstring][]=new Mededeling($mededeling['id']);
		}
		return $mededelingen;
	}
	
	public static function getAantal(){
		$db=MySql::instance();
		$priveClause=$verborgenClause="";
		$verborgenClause="zichtbaarheid='zichtbaar'";
		if( Mededeling::isModerator() )
			$verborgenClause="zichtbaarheid!='verwijderd'";
		if( !LoginLid::instance()->hasPermission('P_LEDEN_READ') )
			$priveClause=" AND prive='0'";
		$aantalQuery="
			SELECT COUNT(*) as aantal
			FROM mededeling
			WHERE ".$verborgenClause.$priveClause;
		$resource=$db->select($aantalQuery);
		$resultaat=$db->next($resource);
		return (int)$resultaat['aantal'];
	}
	
	public function getPaginaNummer(){
		$db=MySql::instance();
		$priveClause=$verborgenClause="";
		$verborgenClause="zichtbaarheid='zichtbaar'";
		if( Mededeling::isModerator() ){
			$verborgenClause="zichtbaarheid!='verwijderd'";
		}
		if( !LoginLid::instance()->hasPermission('P_LEDEN_READ') ){
			$priveClause=" AND prive='0'";
		}
		$positieQuery="
			SELECT COUNT(*) as positie
			FROM mededeling
			WHERE datum >= '".$this->getDatum()."' AND ".$verborgenClause.$priveClause;
		$resource=$db->select($positieQuery);
		$record=$db->next($resource);
		$paginaNummer=ceil(($record['positie'])/MededelingenContent::aantalPerPagina);
		$paginaNummer=$paginaNummer >= 1 ? $paginaNummer : 1; // Het moet natuurlijk wel groter dan 0 zijn.
		return $paginaNummer;
	}

//	public static function getNewest(){
//		$db=MySql::instance();
//		$newestQuery="
//			SELECT id
//			FROM mededeling
//			WHERE prioriteit = '1' AND verwijderd='0' AND zichtbaarheid='zichtbaar'
//			ORDER BY datum DESC, id DESC;";
//		$newest=$db->getRow($newestQuery);
//		if(is_array($newest)){
//			return new Mededeling($newest['id']);
//		}
//		return null;
//	}
	public function resetPrioriteit(){
		$updatePrioriteit="
			UPDATE mededeling
			SET	prioriteit='".Mededeling::defaultPrioriteit."'
			WHERE prioriteit='".$this->getPrioriteit()."';";
		return MySql::instance()->query($updatePrioriteit);
	}
	public static function getPrioriteiten(){
		$prioriteiten=array();
		$prioriteiten[255]='geen';
		for($i=1; $i<=6; $i++){
			$prioriteiten[$i]='Top '.$i;
		}
		return $prioriteiten;
	}
	
	// function magBewerken()
	// post: geeft true terug als het huidige lid deze Mededeling mag bewerken of verwijderen. Anders, false.
	public function magBewerken(){
		// het huidige lid mag dit bericht alleen bewerken als hij moderator is of als dit zijn eigen bericht
		// is (en hij dus het toevoeg-recht heeft).
		return Mededeling::isModerator() OR
		(Mededeling::magToevoegen() AND $this->getUid()==LoginLid::instance()->getUid());
	}
	
	public static function isModerator(){ return LoginLid::instance()->hasPermission('P_NEWS_MOD'); }

	// function magToevoegen()
	// post: geeft true terug als het huidige lid Mededelingen mag toevoegen.
	public static function magToevoegen(){ return LoginLid::instance()->hasPermission('P_NEWS_POST'); }
	
	public static function knipTekst($sTekst, $iMaxTekensPerRegel=26, $iMaxRegels=2)
	{
		$iTekensOver=$iMaxTekensPerRegel; // Aantal tekens die over zijn voor de huidige (resultaat)regel.
		$iRegelsOver=$iMaxRegels-1; // Aantal (resultaat)regels die nu over/leeg zijn.
		$sRegelAfsluiting='<br />';

		$sResultaat='';
		$aRegelsInTekst=explode($sRegelAfsluiting, $sTekst);
		// Per (bron)regel (volgens de newlines in $sTekst)
		for($i=0; $i<$iMaxRegels AND $i<count($aRegelsInTekst); $i++){
			$sRegel=$aRegelsInTekst[$i];
			$iRegelLengte=strlen(strip_tags($aRegelsInTekst[$i])); // Wel even de tags eruit slopen, want we moeten niet vals spelen.
			if($iRegelLengte<=$iTekensOver){ // Er is genoeg plek op de huidige (resultaat)regel.
				// Bronregel toevoegen aan de resultaatregel.
				$sResultaat.=$sRegel;
				// Nieuwe (resultaat)regel markeren.
				$iRegelsOver--;
				$iTekensOver=$iMaxTekensPerRegel;
			}else{ // Er is niet genoeg plek op de huidige regel.
				// Alle woorden printen die nog passen.
				$aWoordenInRegel=explode(' ', $sRegel);
				// Per woord in deze regel.
				foreach($aWoordenInRegel as $sWoord){
					$aTagsInWoord = explode('<', $sWoord);
					// Per tag in dit woord.
					for($k=0; $k<count($aTagsInWoord); $k++){
						$sTag=$aTagsInWoord[$k];
						$iPositieEindTag=strpos($sTag, '>');
						// De woordlengte bepalen.
						if($iPositieEindTag===false){ // De tag is nog niet beëindigd.
							if($k!=0){ // De eerste moeten we nooit als tag zien.
								$iWoordLengte=0;
							}else{ // Maar bij de eerste moeten er wel tekens vanaf getrokken worden.
								$iWoordLengte=strlen($sTag);
							}
						}else{ // De tag wordt wél beëindigd.
							$iWoordLengte=strlen($sTag) - ($iPositieEindTag+1); // De lengte v/d string ná de tag.
						}
						if(	($ampPos=strpos($sTag, '&')	)!==false AND
							($semiPos=strpos($sTag, ';'))!==false AND
							($diff=$semiPos-$ampPos		)>=3 AND
							$diff<=7
						){
							//Dus, als er een enkele entiteit in $sTag zit, corrigeren we de woordlengte. We definiëren
							//een entiteit als een string die begint met een '&', eindigt met een ';' met daartussen 2 tot 6
							//karakters.
							$iWoordLengte-=$diff;
						}

						// En nu gaan we kijken of het woord past.
						if($iWoordLengte+1<=$iTekensOver){
							// Het woord past, dus toevoegen.
							if($k!=0){ $sResultaat.='<'; }
							$sResultaat.=$sTag;
							$iTekensOver-=$iWoordLengte+1;
						}else if($iWoordLengte<=$iMaxTekensPerRegel AND $iRegelsOver>0){ // Het woord past op de volgende regel.
							// Woord toevoegen.
							if($k!=0){ $sResultaat.='<'; }
							$sResultaat.=$sTag;
							// Nieuwe regel markeren.
							$iRegelsOver--;
							$iTekensOver=$iMaxTekensPerRegel-$iWoordLengte;
							$i++;
						}else{ // Het woord past niet op deze regel én niet op een (eventuele) volgende regel.
							if(substr($sTag, 0, 1) == '/' AND $iPositieEindTag!==false){ // Er wordt een tag beëindigd! Even printen dus.
								$sResultaat.='<'.substr($sTag,0,$iPositieEindTag);
							}
							$sResultaat.='…';
							$bStopDeBoel=true;
							break;
						}
					} // Einde iedere tag in dit woord.
					if(isset($bStopDeBoel) AND $bStopDeBoel)
						break;
					$sResultaat.=' ';
				} // Einde ieder woord in deze regel.
			}
			$sResultaat.=$sRegelAfsluiting;
		} // Einde iedere (bron)regel.

		// Indien het resultaat eindigt op een regelafsluiting-tag, halen we die even weg.
		$iLengteRegelAfsluiting=strlen($sRegelAfsluiting);
		if(substr($sResultaat, strlen($sResultaat)-$iLengteRegelAfsluiting, $iLengteRegelAfsluiting) == $sRegelAfsluiting){
			$sResultaat=substr($sResultaat, 0, strlen($sResultaat)-$iLengteRegelAfsluiting);
		}
		if($iRegelsOver<=0 AND isset($aRegelsInTekst[$i+1])){ // Indien er geen regels meer over zijn, maar wel tekst.
			$sEind=substr($sResultaat, strlen($sResultaat)-1, 1); // Laatste teken ophalen.
			// Alleen de puntjes erachter zetten als dit nog niet gedaan is doordat er een woord niet meer paste.
			if($sEind!='…'){
				$sResultaat.='…';
			}
		}
		return $sResultaat;
	}
}

?>
