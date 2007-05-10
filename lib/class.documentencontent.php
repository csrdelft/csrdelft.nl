<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.documentencontent.php
# -------------------------------------------------------------------
# Historie:
# 30-01-2006 Matthijs Neven
# 13-11-2006 Maarten Somhorst: compleet veranderd.

class DocumentenContent extends SimpleHTML {

	var $_documenten;
	var $_db;
	var $_lid;
	
	var $limit;
	var $colspan;
	var $maxFilesize;

	function DocumentenContent(&$documenten) {
		$this->_documenten =& $documenten;
		$this->_lid =$documenten->_lid;
		$this->_db =& $documenten->_db;
		
		// TODO: verkrijgen van documenten.php ?!?!?
		$this->colspan=4;
		$this->limit=5;
		$this->maxFilesize=10*1024*1024;
	}

	function getTitel() {
		return 'Documenten';
	}
	
	function viewWaarBenik() {
		echo '<a href="/intern/">Intern</a> &raquo; '.$this->getTitel();
	}
	
	/*
	 * Laat de box zien waarmee men documenten kan bewerken
	 * parameters: 	catsOptions is een string met de opties van het drop-down menu
	 * 				mode is een string (edit | add)
	 * 				id is het id van het document
	 * 				name is de titel van het document
	 * 				singleCat is de categorie-id indien er een enkele categorie weergegeven wordt
	 */
	function getBox($catsOptions, $mode, $id, $catid, $name, $singleCat){
		// handige variabeles
		$cellStart = '
			<td colspan="'.$this->colspan.'">
			<span id="docBox" class="box">
		';
		$cellEnd = '
			</span>
			</td>
			</tr>
		';

		if(!$singleCat){
			$annuleerLink='window.location=\'../\'';
		} else {
			$annuleerLink='window.location=\'../../categorie/'.$singleCat.'\'';
		}
		
		if($mode == 'edit') {
			// zorgen dat het formulier ook goed gaat bij SingleCat-mode!
			if(!$singleCat){
				$action='../#bewerk';
			} else {
				$action='../../#bewerk';
			}
			$res = '
			<tr>
			'.$cellStart.'
			<span class="boxTitle">Documentgegevens bewerken</span>
			<p>
			<form enctype="multipart/form-data" action="'.$action.'" method="POST">
			<input type="hidden" name="documentid" value="'.$id.'" />

			<div class="boxContent">Nieuwe titel:</div>
			<span><input type="text" name="title" id="docTitle" value="'.$name.'"/></span>
			<br>
			<div class="boxContent">Nieuwe categorie:</div>
			<span><select name="cat">'.$catsOptions.'</select></span>
			<p>
			<span><input type="submit" value="Verstuur" /></span>
			<span><input type="button" value="Annuleren" onClick="'.$annuleerLink.'"/></span>
			</form>
			'.$cellEnd;
		} else if($mode == 'add') {
			if(!$singleCat){
				$action='../#voegtoe';
			} else {
				$action='../../#voegtoe';
			}
			$res = '
			<tr>
			'.$cellStart.'
			<span class="boxTitle">Formaat toevoegen</span>
			<p>
			<form enctype="multipart/form-data" action="'.$action.'" method="POST">
			<input type="hidden" name="documentid" value="'.$id.'" />
			<input type="hidden" name="cat" value="'.$catid.'" />
			<input type="hidden" name="MAX_FILE_SIZE" value="'.$this->maxFilesize.'" />

			<div class="boxContent">Nieuw bestand:</div>
			<span><input type="file" name="file" size=30 /></span>
			<p>
			<span><input type="submit" value="Verstuur" /></span>
			<span><input type="button" value="Annuleren" onClick="'.$annuleerLink.'"/></span>
			</form>
			'.$cellEnd;
		} else {
			$res = '<tr><td>Error in '.get_class($this).'::getBox!!</tr></td>';
		}
		return $res;
	}

	function view() {		
		// titel
		echo '<h1>Documenten</h1><p>'."\n";

		// bepalen of het een single-mode is of niet
		$singleCat=0; // boolean/catid!!
		if(isset( $_GET['categorie'])){
			// ofterwijl: als er in de GET duidelijk is gemaakt dat er een enkele categorie
			// zichtbaar gemaakt moet worden
			$singleCat= (int)$_GET['categorie'];
		}

		// alleen voor Documenten-moderators
		if ($this->_lid->hasPermission('P_DOCS_MOD')) {
			// $_POST en $_GET uitlezen
			if(isset($_GET['mode'], $_GET['id'])){ // als er een mode ge-activeerd is
				$mode=$_GET['mode'];
				$id=(int)$_GET['id'];
				
				if($mode == 'del') { // als de mode 'verwijderen' is: document verwijderen
					$successful = $this->_documenten->deleteDocument($id);
//					$successful ? $successful = true : $successful = false;
				}
				
				// document-id waar het om gaat opslaan voor later gebruik
				$boxDocId=$id;
			} else if(isset($_POST['documentid'], $_POST['title'], $_POST['cat']) && is_string($_POST['title'])) {
				// als er nieuwe data binnen is over een document
				$mode='edit';
				$id=(int)$_POST['documentid'];
				$successful = $this->_documenten->updateDocument(
					$id,
					mb_htmlentities($_POST['title']),
					(int)$_POST['cat']
				);

				// POST-data opslaan voor later gebruik als het niet gelukt is, zodat het formuliertje niet opnieuw ingevuld hoeft te worden
				if(!$successful){
					$boxPOST=array($_POST['title'],(int)$_POST['cat']);
				}

				// document-id waar het om gaat opslaan
				$boxDocId=$id;
				// TODO: $boxCatId alvast vaststellen?
			} else if(isset($_POST['documentid'], $_FILES['file'])){
				$mode='add';
				$id=(int)$_POST['documentid'];
				$successful = $this->_documenten->performUpload($id);
			} else if(isset($_GET['mode'], $_GET['successful']) && $_GET['mode']=='uploaded') { // ofwel: als er met succes een document is ge-upload
				$mode=$_GET['mode'];
				if($_GET['successful']=='true'){
					$successful=true;
				} // nog niets voor 'false'...
			}
				
			if($mode == 'edit' || $mode=='add' ){
				$boxCatId=$this->_documenten->getCatIDByDocumentID($id);
			}
			
			// overbodig. Verwijderen om verwarring te voorkomen
			unset($id);

			// zorgt dat er juist ge-linkt wordt
			$sLinkPrefix='';
			if( isset($mode) && (!isset($_POST) || empty($_POST)) ){
				// de browser denkt dat we in bijvoorbeeld ..documenten/bewerken/xx
				// zitten, dus elke link moet een map terug
				$sLinkPrefix.='../';
			} else if(strstr($_SERVER['REQUEST_URI'],'documenten') == 'documenten'){
				// als de url eindigt op /documenten (let op de slash aan het eind)
				$sLinkPrefix.='documenten/';
			}
			
			// als we 1 categorie laten zien, moeten we (nog) een stapje terug, omdat we in
			// ..documenten/categorie/xx OF in ..documenten/bewerken/xx/yy zitten.
			// In het laatste geval is de mode 'edit' actief, dus is de link hierboven ook al aangepast.
			if($singleCat){
				$sLinkPrefix='../'.$sLinkPrefix;
			}

			// Toevoeg-link weergeven
			echo '<a href="'.$sLinkPrefix.'toevoegen/">Documenten toevoegen</a><p>'."\n";
		}
		
		if($singleCat){
			echo '<a href="'.$sLinkPrefix.'">Terug naar volledig overzicht</a><p>'."\n";
		}

		// documenten ophalen
		$aDocumenten = $this->_documenten->getDocumenten($singleCat);

		echo '<table class="doctable">'."\n";
		
		// per categorie doorlopen
		for($i=0; $i<count($aDocumenten); $i++) {
			$catname=$aDocumenten[$i][0];
			$catid=$aDocumenten[$i][1];
			// het aantal documenten in deze categorie
			$count=count($aDocumenten[$i]);

			// titel van een categorie
			$title='<tr><td colspan="'.$this->colspan.'" class="dochoofd"><strong>';
			// de categorie 'jump-able' maken indien nodig
			if(isset($boxCatId) && $catid == $boxCatId){
				$title.='<a id="cattitle" name="bewerk">'.$catname.'</a>';
			} else if($mode=='add' && !(isset($successful) && $successful==true)){ // als de 'Formaat toevoegen'-box weergegeven wordt of het mislukt is
				$title.='<a id="cattitle" name="voegtoe">'.$catname.'</a>';
			} else {
				$title.=$catname;
			}
			$title.='</strong></td></tr>'."\n";
			echo $title;
			 
			// TODO: kijken of $boxDocId bij de eerste paar zit?!
			
			// de index berekenen die bepaalt hoeveel documenten er maximaal weergegeven worden
			// aanname: $this->limit is set.
			$showAll=false;
			if( $singleCat || $catid==$boxCatId ){
				$maxIndex=$count; // TODO: andere naam verzinnen voor maxIndex
				$showAll=true;
			} else {
				$maxIndex=$this->limit+2; // die twee omdat $j in de for-loop hieronder op 2 begint.
			}
			
//			if( !$singleCat || !isset($boxCatId) || $catid==$boxCatId){ //|| !($mode=='add' && isset($successful) && $catid==$boxCatId))){
//				// ofwel: als het normale overzicht gegeven wordt en de huidige categorie NIET
//				// per se volledig in zicht hoeft te zijn.
//				$maxIndex=$this->limit+2; // die twee omdat $j in de for-loop hieronder op 2 begint.
//			} else {
//				$maxIndex=$count;
//			}
			
			// per document de categorie doorlopen
			// j=2, omdat daar de documenten pas beginnen.
			for($j=2; $j<$count && $j<$maxIndex; $j++) {
				$docid = $aDocumenten[$i][$j]['ID'];
				$name = $aDocumenten[$i][$j]['naam'];
				$datum = $aDocumenten[$i][$j]['datum'];
				$aExtensions = $this->_documenten->getExtensionsByID($docid);

				// link(s) maken
				$slink='';
				for($k=0; $k<count($aExtensions); $k++) {
					$fileid = $aExtensions[$k]['id'];
					$fileext = $aExtensions[$k]['extensie'];

					$slink.='<a href="'.$sLinkPrefix.'neerladen/'.$fileid.'">'.$fileext.'</a> ';
				}

    			// het document laten zien in de tabel
				$row = '<tr><td>'.$name.'</td><td>'.$slink.'</td><td>'.$datum.'</td>';

				// links voor bewerken, toevoegen en verwijderen, natuurlijk alleen voor Documenten-moderators
				if ($this->_lid->hasPermission('P_DOCS_MOD')) {
					$escapedName=addslashes(html_entity_decode($name, ENT_NOQUOTES, 'UTF-8'));
					$confirmString = 'Weet je zeker dat je \\\''.$escapedName.'\\\' wilt verwijderen?';
					
					$row .= '<td class="buttoncell">'."\n"

					// bewerken:
					.'<a href="'.$sLinkPrefix.'bewerken/'.$docid;
					// als er een enkele categorie weergegeven wordt, moet dat ook het geval zijn als er op de
					// link geklikt is.
					if($singleCat){
						$row.='/'.$catid;
					}
					$row.='#bewerk">'."\n" // de jump-ketser
					.'<img class="button" src="http://plaetjes.csrdelft.nl/forum/bewerken.png" /></a>'."\n"

					// verwijderen:
					.'<a onclick="return confirm(\''.$confirmString.'\')" href="'.$sLinkPrefix.'verwijderen/'.$docid.'">'."\n"
					.'<img class="button" src="http://plaetjes.csrdelft.nl/forum/verwijderen.png" /></a>'."\n"

					// toevoegen:
					.'<a href="'.$sLinkPrefix.'toevoegen/'.$docid;
					// als er een enkele categorie weergegeven wordt, moet dat ook het geval zijn als er op de
					// link geklikt is.
					if($singleCat){
						$row.='/'.$catid;
					}
					$row.='#voegtoe">'."\n"
					.'<img class="button" src="http://plaetjes.csrdelft.nl/documenten/plus.jpg" /></a>'."\n"

					.'</td></tr>'."\n";
					
					// de rij (box) voor het daadwerkelijk bewerken of toevoegen
					if(isset($boxDocId, $mode) && $boxDocId == $docid && $mode != 'del'
						&& (!isset($successful) || !$successful)) {
							// ofterwijl: er is een mode en een docid ingesteld, het docid is gelijk aan de huidige id,
							// de mode is niet 'del' (document verwijderen) en de modus is nog niet voltooid
							
							// twee mogelijkheden: de nieuw ingevoerde gegevens of de normale gegevens
							if(isset($boxPOST)){
								$boxTitle=$boxPOST[0];
								$boxCat=$boxPOST[1];
							} else {
								$boxTitle=$name;
								$boxCat=$catid;
							}
							// de rij printen m.b.v. de getBox-methode
							$row .= $this->getBox($this->_documenten->getCatsOptions($boxCat), $mode, $docid, $catid, $boxTitle, $singleCat);
					}
				} else {
					$row .= '</tr>'."\n";
				}
				echo $row;
			}
			
			// "meer..." weergeven (of niet)
			if( !$singleCat && ((!$showAll && $catid==$boxCatId && ($count-2)>$this->limit) || (($count-2)>$this->limit && $catid!=$boxCatId))){
//			if( !$singleCat && ($count-2) > $this->limit &&
//				(!isset($boxCatId) || ($catid==$boxCatId && ($mode!='edit' || !isset($_POST['oldDocument'])) || ($catid!=$boxCatId)) ){
					// -2 vanwege $catname en $catid (zie boven)
				// de laatste twee expressies zorgen er voor dat de categorie die al volledig
				// weergegeven worden (in Edit-mode bijvoorbeeld), geen overbodige meer...-knop heeft

				echo '<tr><td><a href="'.$sLinkPrefix.'categorie/'.$catid.'">meer...</a></td></tr>'."\n";
			}
			unset($showAll);
			echo '<tr><td>&nbsp;</td></tr>'."\n";
		}
		echo '</table>'."\n";
		
		// eventuele melding weergeven (in een alert)
		if(isset($mode, $successful)) { // een modus is actief
			$message='';
			if($mode=='edit'){
				if($successful) { // het is gelukt
					$message='De bewerking is doorgevoerd.';
				} else {
					$message='Het bewerken is niet gelukt!';
				}
			} else if($mode=='del'){
				$sDocName=$this->_documenten->getDocumentNameById($boxDocId); // TODO: ook opvragen bij 'edit' (hierboven)?
				if($successful) { // het is gelukt
					$message='Het document \''.addslashes(html_entity_decode($sDocName, ENT_QUOTES, 'UTF-8')).'\' is verwijderd.';
				} else {
					$message='Het document \''.addslashes(html_entity_decode($sDocName, ENT_QUOTES, 'UTF-8')).'\' is niet verwijderd!';
				}
			} else if($mode=='add'){
				if($successful) { // het is gelukt
					$message='Het bestand is toegevoegd.';
				} else {
					$message='Het bestand is niet toegevoegd!';
				}
			} else if($mode=='uploaded'){
				if($successful) { // het is gelukt
					$message='Het document is toegevoegd.';
				}
			}

		// een paar regeltjes JavaScript om ervoor te zorgen dat de alert pas komt als de pagina volledig geladen is.
		echo '<script language="JavaScript">function naHetLaden(){ alert("'.$message.'") } '."\n";
		echo 'window.onload=naHetLaden</script>'."\n";
		}
		unset($successful);
	}
}
?>
