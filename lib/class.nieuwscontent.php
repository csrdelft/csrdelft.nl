<?php
# C.S.R. Delft
# -------------------------------------------------------------------
# class.nieuwscontent.php
# -------------------------------------------------------------------
# Beeldt de berichten af die in een Nieuws-object zitten.
# -------------------------------------------------------------------


require_once ('class.nieuws.php');
define('NIEUWS_ROOT', '/actueel/mededelingen/');

class NieuwsContent extends SimpleHTML {

	# de objecten die data leveren
	private $_nieuws;
	private $ubb;
	
	private $_sError='';
	
	private $_berichtID;
	private $_actie='overzicht';

	public function NieuwsContent (&$nieuws) {
		$this->_nieuws =& $nieuws;
		$this->ubb= new csrubb();
	}

	private function getNieuwBerichtLink(){
		if($this->_nieuws->isNieuwsMod()){
			return '<a href="'.NIEUWS_ROOT.'toevoegen" class="knop">Nieuwe mededeling</a>';
		}
	}
	private function getBerichtModControls($iBerichtID){
		if($this->_nieuws->isNieuwsMod()){
			return	'<a href="'.NIEUWS_ROOT.'bewerken/'.$iBerichtID.'"><img src="'.CSR_PICS.'forum/bewerken.png'.'" alt="bewerken" /></a> '.
					'<a href="'.NIEUWS_ROOT.'verwijderen/'.$iBerichtID.'" onclick="return confirm(\'Weet u zeker dat u dit nieuwsbericht wilt verwijderen?\')"><img src="'.CSR_PICS.'forum/verwijderen.png'.'" alt="verwijderen" /></a>';
		}
	}
	private function getCategorieSelect($geselecteerdeCategorie=0){
		$resultaat='<select name="categorie">';
		$aCategorieen=$this->_nieuws->getCategorieen();
		if(!is_array($aCategorieen) or empty($aCategorieen)){ return ''; }
		if($geselecteerdeCategorie==0){ // Indien er geen categorie geselecteerd is
			$resultaat.='<option value="0">Selecteer...</option>';
		}
		foreach($aCategorieen as $aCategorie){
			$resultaat.='<option value="'.$aCategorie['id'].'"';
			if($aCategorie['id']==$geselecteerdeCategorie){
				$resultaat.=' selected="selected"';
			}
			$resultaat.='>'.$aCategorie['naam'].'</option>';
		}
		$resultaat.='</select>';
		return $resultaat;
	}
	private function getTopSelect($geselecteerdeTop=0){
		// Hoeveel 'top-mededelingen' hebben we? Moet groter of gelijk aan één (1) zijn.
		$iTopMax=$this->_nieuws->getAantalTopBerichten() + $this->_nieuws->getTopBerichtenSpeling();
		$sResultaat='<select name="rank">';
		if($geselecteerdeTop==0 OR $geselecteerdeTop>$iTopMax){
			$sResultaat.='<option value="'.$this->_nieuws->getStandaardRank().'">geen</option>';
		}
		for($i=1;$i<=$iTopMax;$i++){
			$sResultaat.='<option value="'.$i.'"';
			if($geselecteerdeTop == $i)
				$sResultaat .= ' selected="selected"';
			$sResultaat .= '>Top '.$i.'</option>';
		}
		$sResultaat.='</select>';
		return $sResultaat;
	}

	private function bewerkFormulier(){
		if($_SERVER['REQUEST_METHOD']!='post'){
			//gegevens direct ophaelen uit database
			$aBericht=$this->_nieuws->getMessage($this->_berichtID, true);
			$titel=$aBericht['titel'];
			$tekst=$aBericht['tekst'];
			$categorie=$aBericht['categorie'];
			$rank=$aBericht['rank'];
			$prive=$verborgen='';
			if($aBericht['prive']==1){ $prive='checked="checked"'; }
			if($aBericht['verborgen']==1){ $verborgen='checked="checked"'; }
		}else{
			//wel een bericht om te bewerken, maar de varabelen uit _POST halen omdat het nog niet 
			//aan de eisen van $this->valideerFormulier() voldeed
			$titel=htmlspecialchars($_POST['titel']);
			$tekst=htmlspecialchars($_POST['tekst']);
			$categorie=(int)$_POST['categorie'];
			$rank=(int)$_POST['rank'];
			$prive=$verborgen='';
			if(isset($_POST['prive'])){ $prive='checked="checked"'; }
			if(isset($_POST['verborgen'])){ $verborgen='checked="checked"'; }
			//voor het plaatje nog eens 
			$aBericht=$this->_nieuws->getMessage($this->_berichtID);
		}
		$sCategorieSelect=$this->getCategorieSelect($categorie);
		
		echo '<form action="'.NIEUWS_ROOT.'bewerken/'.$this->_berichtID.'" method="post" enctype="multipart/form-data">';
		echo '<div class="pubciemail-form">';
		echo $this->getMelding();
		echo '<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="'.$titel.'" style="width: 100%;" /><br />
		<strong>Bericht</strong>&nbsp;&nbsp;';
		// link om het tekst-vak groter te maken.
		echo '<a href="#" onclick="vergrootTextarea(\'nieuwsBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';
		echo '<textarea id="nieuwsBericht" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">'.$tekst.'</textarea><br />';
		echo '<div style="height: 200px; width: 30%; float: left;">Dit bericht…<br />';
		echo '<input id="prive" type="checkbox" name="prive" '.$prive.' /><label for="prive">…alleen weergeven bij leden</label><br />';
		echo '<input id="verborgen" type="checkbox" name="verborgen" '.$verborgen.' /><label for="verborgen">…verbergen</label><br />';
		echo '<br />Categorie: '.$sCategorieSelect;
		echo '<br />Markering: '.$this->getTopSelect($rank).'<br />';
		echo '</div>';
		echo '<div style="height: 200px; width: 70%; float: right; ">';
		if(trim($aBericht['plaatje'])!=''){
			echo '<img src="'.CSR_PICS.'nieuws/'.$aBericht['plaatje'].'" width="200px" height="200px" alt="Afbeelding" style="float: left; margin-right: 10px;" />';
		}
		echo '<strong>Afbeelding bij de mededeling</strong><br />';
		//input ding om een plaatje toe te voegen...
		echo 'Afbeelding toevoegen of vervangen:<br /><input type="file" name="plaatje" size="40" /><br />';
		echo '<span class="waarschuwing">(png, gif of jpg, 200x200 of groter in die verhouding.)</span></div>';
		echo '<input type="submit" name="submit" value="opslaan" />&nbsp;<a href="'.NIEUWS_ROOT.$this->_berichtID.'" class="knop">annuleren</a></div>';
	}
	private function nieuwFormulier(){
		$titel=$tekst=$prive=$verborgen='';
		$categorie=0;
		$rank=$this->_nieuws->getStandaardRank();
		if(isset($_POST['titel'])){ $titel=htmlspecialchars($_POST['titel']); }
		if(isset($_POST['tekst'])){ $tekst=htmlspecialchars($_POST['tekst']); }
		if(isset($_POST['categorie'])){ $categorie=(int)$_POST['categorie']; }
		if(isset($_POST['rank'])){ $rank=(int)$_POST['rank']; }
		if(isset($_POST['prive'])){ $prive='checked="checked"'; }
		if(isset($_POST['verborgen'])){ $verborgen='checked="checked"'; }
		echo '<form action="'.NIEUWS_ROOT.'toevoegen" method="post"><div class="pubciemail-form">';
		echo $this->getMelding();
		echo '<strong>Titel</strong><br />
		<input type="text" name="titel" class="tekst" value="'.$titel.'" style="width: 100%;" /><br />
		<strong>Bericht</strong>&nbsp;&nbsp;';
		// link om het tekst-vak groter te maken.
		echo '<a href="#" onclick="vergrootTextarea(\'nieuwsBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';
		echo '<textarea id="nieuwsBericht" name="tekst" cols="80" rows="10" style="width: 100%" class="tekst">'.$tekst.'</textarea><br />';
		echo '<input id="prive" type="checkbox" name="prive" '.$prive.' /><label for="prive">Dit bericht alleen weergeven voor leden</label>&nbsp;';
		echo '<input id="verborgen" type="checkbox" name="verborgen" '.$verborgen.' /><label for="verborgen">Dit bericht verbergen</label><br />';
		echo '<br />Categorie: '.$this->getCategorieSelect($categorie).'<br />';
		echo 'Markering: '.$this->getTopSelect($rank).'<br />';
		echo '<input type="submit" name="submit" value="opslaan"  />&nbsp;<a href="'.NIEUWS_ROOT.'" class="knop">annuleren</a></div>';
	}
	public function valideerFormulier(){
		$bNoError=true;
		if(!(isset($_POST['titel']) AND isset($_POST['tekst']) AND isset($_POST['categorie']) AND isset($_POST['rank']))){
			$bNoError=false;
			$this->setMelding('Formulier is niet compleet<br />');
		}else{
			if(strlen($_POST['titel'])<2){
				$bNoError=false;
				$this->setMelding('Het veld <strong>titel</strong> moet minstens 2 tekens bevatten.<br />');
			}
			if(strlen($_POST['tekst'])<5){
				$bNoError=false;
				$this->setMelding('Het veld <strong>tekst</strong> moet minstens 5 tekens bevatten.<br />');
			}
			if((int)$_POST['categorie'] <= 0){
				$bNoError=false;
				$this->setMelding('Er moet een <strong>categorie</strong> geselecteerd zijn.<br />');
			}
			if((int)$_POST['rank'] <=0 OR (int)$_POST['rank']>$this->_nieuws->getStandaardRank()){
				$bNoError=false;
				$this->setMelding('De <strong>markering</strong> is onjuist opgegeven.<br />');
			}
		}
		return $bNoError;
	}
	private function getBericht(){
		$aBericht=$this->_nieuws->getMessage($this->_berichtID, true);
		if(is_array($aBericht)){
			echo '<div class="nieuwsbody"><div class="nieuwstitel">';
			//verborgen berichten aangeven, enkel bij mensen met P_NEWS_MOD
			if($aBericht['verborgen']=='1'){ echo '<em>[verborgen] </em>';	}
			echo mb_htmlentities($aBericht['titel']).'</div>';
			//nieuwsbeheer functie dingen:
			if($aBericht['plaatje']!=''){
				echo '<img class="nieuwsplaatje" src="'.CSR_PICS.'nieuws/'.$aBericht['plaatje'].'" width="200px" height="200px" alt="'.$aBericht['plaatje'].'" />';
			}
			echo '<i>('.date('d-m-Y H:i', $aBericht['datum']).')</i> ';
			echo '<br />'.$this->ubb->getHTML($aBericht['tekst']).'<br />';
			echo '</div>';
			echo $this->getBerichtModControls($aBericht['id']);
		}else{
			echo 'Dit bericht bestaat niet, of is enkel zichtbaar voor ingelogde gebruikers.';
		}
	}
	private function viewBelangrijksteMededelingBlock()
	{
		$aBericht=$this->_nieuws->getTop(1);
		if(is_array($aBericht) AND !empty($aBericht)){
			echo '<a href="'.NIEUWS_ROOT.$aBericht['id'].'"><div id="top1mededeling">';
			echo '<img src="'.CSR_PICS.'nieuws/'.$aBericht['plaatje'].'" width="200px" height="200px" alt="'.$aBericht['titel'].'" />';
			echo '<div id="titel">'.mb_htmlentities($this->knipTekst($aBericht['titel'], 26, 2)).'</div>';
			echo '</div></a>';
		}
	}
	public function getTopBlock(){
		$sResultaat='';
		$aBerichten=$this->_nieuws->getTop($this->_nieuws->getAantalTopBerichten());
		if(is_array($aBerichten) AND !empty($aBerichten)){
			foreach($aBerichten as $aBericht){
				$sLink = '<a href="'.NIEUWS_ROOT.$aBericht['id'].'" title="'.mb_htmlentities($aBericht['titel']).'">';
				$sResultaat.='<div class="mededeling-grotebalk">';
				$sResultaat.='<div class="plaatje">'.$sLink;
				$sResultaat.='<img src="'.CSR_PICS.'nieuws/'.$aBericht['plaatje'].'" width="70px" height="70px" alt="'.$this->knipTekst(mb_htmlentities($aBericht['titel'],10,5)).'" />';
				$sResultaat.='</a></div>';
				$sResultaat.='<div class="titel">'.$sLink.$this->knipTekst(mb_htmlentities($aBericht['titel']), 34, 1).'</a></div>';
				$sResultaat.='<div class="bericht">'.$this->knipTekst($this->ubb->getHTML($aBericht['tekst']), 46, 4).'</div>';
				$sResultaat.='</div>';
			}
		}
		return $sResultaat;
	}
	private function viewOverzicht()
	{
		$lid=Lid::get_lid();
		
		// berichtID setten als dat nog niet gedaan is.
		if(empty($this->_berichtID))
			$this->_berichtID = $this->_nieuws->getBelangrijksteMededelingId();

		$includeVerborgen=false;
		if($lid->hasPermission('P_NEWS_MOD')){ $includeVerborgen=true; }
		$aBerichten=$this->_nieuws->getMessages(0, $includeVerborgen);
		
		echo '<div class="mededelingen-overzichtlijst">';
		$this->getOverzichtLijst($aBerichten);
		echo '</div>';
//		echo '<div style="width: 400px; float: left;">';
		echo '<div class="nieuwsbericht">';
		$this->getBericht();
		echo '</div>';
		echo '<div id="mededelingen-top3block">';
		echo $this->getTopBlock();
		echo '</div>';
//		echo '</div>';
	}

	private function getOverzichtLijst(array $aBerichten)
	{
		if(!is_array($aBerichten) OR empty($aBerichten)) {	
			echo 'Zoals het is, zoals het was, o Civitas!<br />(Geen mededelingen gevonden dus…)<br /><br />';
		}else{
			$bEersteRecord=true;
			$iHuidigeJaarWeeknummer=date('oW')+1; // Volgende week.
			foreach ($aBerichten as $aBericht) {
				$iJaarWeeknummer=date('oW', $aBericht['datum']); // De week van dit record (yyyymm)
				if($iJaarWeeknummer < $iHuidigeJaarWeeknummer){ // Indien we een andere week aan het printen zijn dan de vorige
					// Voor de eerste keer niets sluiten.
					if(!$bEersteRecord) { echo '</div>'; }
					else { $bEersteRecord=false; }
					// Nieuw blok beginnen.
					echo '<div class="mededelingenlijst-block">';
					// Even casten om de 0 ervoor weg te halen (bijvoorbeeld bij week 05).
					echo '<div class="mededelingenlijst-block-titel">Week '.(int)date('W', $aBericht['datum']).'</div>';
					$iHuidigeJaarWeeknummer = $iJaarWeeknummer;
				}
				$id='';
				$class='mededelingenlijst-item';
				if($aBericht['verborgen']=='1'){
					$class.=' verborgen-item';
				}
				if($aBericht['id']==$this->_berichtID){
					$id.='id="actief" ';
				}
				echo '<div '.$id.'class="'.$class.'">';
				if($aBericht['categorieplaatje']!=''){
					echo '<div class="mededelingenlijst-plaatje"><a href="'.NIEUWS_ROOT.$aBericht['id'].'">
						<img src="'.CSR_PICS.'nieuws/'.$aBericht['categorieplaatje'].'" width="10px" height="10px" alt="'.$aBericht['categorienaam'].'" /></a></div>';
				}
				$sDate=date('(d-m)',$aBericht['datum']);
				echo '<div class="itemtitel">'.$sDate.' <a href="'.NIEUWS_ROOT.$aBericht['id'].'">';
				echo $this->knipTekst(mb_htmlentities($aBericht['titel']), 35, 1).'</a></div>';
				echo '</div>'; // mededelingenlijst-item
			}//einde foreach bericht
			echo '</div>'; //sluit laatste block
		}
		echo '<br />'.$this->getNieuwBerichtLink();
	}
	
	function getLaatsteMededelingen(){
		$aBerichten=$this->_nieuws->getMessages(0,false,8);
		echo '<h1><a href="/actueel/mededelingen/">Mededelingen</a></h1>';
		foreach($aBerichten as $aBericht){
			$titel=mb_htmlentities($aBericht['titel']);
			if(strlen($titel)>21){
				$titel=str_replace(' ', '&nbsp;', trim(substr($titel, 0, 18)).'…');
			}
			$bericht=preg_replace('/(\[(|\/)\w+\])/', '|', $aBericht['tekst']);
			$berichtfragment=substr(str_replace(array("\n", "\r", ' '), ' ', $bericht), 0, 40);
			echo '<div class="item"><span class="tijd">'.date('d-m', $aBericht['datum']).'</span>&nbsp;';
			echo '<a href="/actueel/mededelingen/'.$aBericht['id'].'" 
				title="['.mb_htmlentities($aBericht['titel']).'] '.
					mb_htmlentities($berichtfragment).'">'.$titel.'</a><br />'."\n";
			echo '</div>';
		}
	}

	/* function knipTekst()
	 * 
	 * Middels deze functie kunnen we precies bepalen hoeveel regels tekst we willen hebben en hoeveel tekens per regel.
	 * Een mooie toepassing is een blokje met een ongedefinieerd aantal tekens. Middels deze functie kun je er voor zorgen
	 * dat de tekst netjes afgekapt wordt na x regels. De functie houdt ook regening met <br /> tags - die bijvoorbeeld
	 * van de UBB-klasse af kunnen komen. Een aantal opmerkingen:
	 * - Tags worden niet gerekend tot 'tekens', dus er wordt voor gezorgd dat deze niet meegeteld worden.
	 * - Er worden alleen <br />'s neergezet tussen regels waar ze al stonden, dus ze worden niet geplaatst als deze functie
	 *	 ziet dat er een nieuwe regel gebruikt moet worden voor de rest v/d tekst. We gaan er van uit dat de browser de tekst
	 *	 netjes naar de volgende regel laat doorlopen.
	 */
	private function knipTekst($sTekst, $iMaxTekensPerRegel=26, $iMaxRegels=2)
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

	public function setBerichtID($iBerichtID){ $this->_berichtID=(int)$iBerichtID; }
	public function setActie($sActie){	$this->_actie=$sActie; }
	
	public function getTitel(){
		$categorie='Actueel';
		return $categorie.' | '.$this->getPaginaTitel();
	}
	
	/* function getPaginaTitel
	 * 
	 * Geeft de titel van de pagina die moet worden laten zien. Deze string wordt gebruikt
	 * als grote titel bovenaan de pagina, maar ook als title in het browservenster. 
	 */
	private function getPaginaTitel(){
		switch($this->_actie){
			case 'bewerken': return 'Mededeling bewerken'; break;
//			case 'bericht': return 'Mededeling'; break;
			case 'toevoegen': return 'Mededeling toevoegen'; break;
			case 'beheer': return 'Mededelingen beheer'; break;
			case 'overzicht': return 'Mededelingen'; break;
		}
	}
	public function view(){
		if($this->_actie!='belangrijkste1' AND $this->_actie!='laatste'){
			echo '<div id="mededelingen-titel"><h1>'.$this->getPaginaTitel().'</h1></div>';
		}
		switch($this->_actie){
			case 'bewerken': $this->bewerkFormulier(); break;
//			case 'bericht': $this->getBericht(); break;
			case 'toevoegen': $this->nieuwFormulier(); break;
			case 'belangrijkste1': $this->viewBelangrijksteMededelingBlock(); break;
			case 'laatste': $this->getLaatsteMededelingen(); break;
			case 'overzicht': default: $this->viewOverzicht(); break;
		}
	}
}

?>
