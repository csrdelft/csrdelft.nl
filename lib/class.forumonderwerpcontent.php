<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerpcontent.php
# -------------------------------------------------------------------


require_once('bbcode/include.bbcode.php');
require_once('class.simplehtml.php');

class ForumOnderwerpContent extends SimpleHTML {
	var $_forum;
	
	//nul als er niets geciteerd wordt, anders een postID
	var $citeerPost=0;
	
	var $_sTitel='forum';
	
	var $_sError=false;
	
	function ForumOnderwerpContent($bForumonderwerp){
		$this->_forum=$bForumonderwerp;
	}

	function getError(){
		if(isset($_SESSION['forum_foutmelding'])){
			$sError='<div id="foutmelding">'.mb_htmlentities(trim($_SESSION['forum_foutmelding'])).'</div>';
			//maar één keer tonen, de melding.
			unset($_SESSION['forum_foutmelding']);
			return $sError;
		}elseif($this->_sError!==false){
			return '<div class="foutmelding">'.$this->_sError.'</div>';
		}
	}
	function setError($sError){
		$this->_sError=trim($sError);
	}
	public function citeer($iPostID){
		//TODO: check of deze post wel bestaat, anders niets citeren.
		$this->citeerPost=(int)$iPostID;
	}
		
	private function getCiteerPost(){
		return $this->citeerPost;
		
	}
	function viewWaarbenik(){
		$sTitel='<a href="/forum/">Forum</a>'.
			' &raquo; <a href="/forum/categorie/'.$this->_forum->getCatID().'">'.$this->_forum->getCatTitel().'</a>'.
			' &raquo; '.$this->_forum->getTitel();
		echo $sTitel;
	}
	function getTitel(){
		$sTitel='Forum - '.
			$this->_forum->getCatTitel().' - '.
			$this->_forum->getTitel();
		return $sTitel;
	}
	function view(){
		//typecasting van de variabelen.
		if($this->_forum->getPosts()===false){
			echo '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>Dit gedeelte van het forum is niet beschikbaar voor u, u zult moeten inloggen, of terug gaan naar <a href="/forum/">het forum</a>';
		}else{
			//show title
			echo '<h2>'.mb_htmlentities($this->_forum->getTitel()).'</h2>';
			//eventuele foutmelding weergeven:
			echo $this->getError();
			//topic mod dingen:
			if($this->_forum->isModerator()){
				echo "\r\n".'U mag dit onderwerp modereren:<br /> ';
				//topic verwijderen
				echo '<a href="/forum/verwijder-onderwerp/'.$this->_forum->getID().'" onclick="return confirm(\'Weet u zeker dat u dit topic wilt verwijderen?\')" class="knop">verwijderen</a> ';
				if($this->_forum->isOpen()){
					$opensluit='sluiten (reageren niet meer mogelijk)';
				}else{
					$opensluit='weer openen (reageren weer w&eacute;l mogelijk)';
				}
				echo ' <a href="/forum/openheid/'.$this->_forum->getID().'" class="knop">'.$opensluit.'</a> ';
				
				if($this->_forum->isPlakkerig()){
					$plakkerigheid='verwijder plakkerigheid';
				}else{
					$plakkerigheid='maak plakkerig';
				}
				echo ' <a href="/forum/plakkerigheid/'.$this->_forum->getID().'" class="knop">'.$plakkerigheid.'</a> ';
				
				echo '<br /><br />'."\r\n";
			}
			echo '<table class="forumtabel"><tr><td class="forumtussenschot" colspan="2"></td></tr>';
			//speciale topic weergeven als het topic er een is. bijvoorbeeld een poll;
			switch($this->_forum->getSoort()){
				case 'T_POLL':
					require_once('class.forumpoll.php');
					require_once('class.pollcontent.php');
					$peiling=new ForumPoll($this->_forum);
					$peilingContent=new PollContent($peiling);
					$peilingContent->view();
				break;
				//hier kunnen nog dingen mee gedaan worden, bijvoorbeeld een andere layout/kleur voor een lezing
				case 'T_LEZING':
				break;
				case 'T_STANDAARD':
				default:
				break;
			}
			
			$iWissel=1;
			foreach($this->_forum->getPosts() as $aBericht){
				echo '<tr><td class="forumauteur">';
				echo $this->_forum->getForumNaam($aBericht['uid'], $aBericht).' schreef ';
				//anker maken met post-ID
				echo '<a id="post'.$aBericht['postID'].'"></a>';
				echo $this->_forum->formatDatum($aBericht['datum']);
				if($aBericht['bewerkDatum']!='0000-00-00 00:00:00'){
					echo ';<br />Bewerkt '.$this->_forum->formatDatum($aBericht['bewerkDatum']);
				}
				echo '<br />';
				//citeer knop enkel als het topic open is en als men mag posten, of als men mod is.
				if($this->_forum->magCiteren()){
					echo ' <a href="/forum/reactie/'.$aBericht['postID'].'#laatste"><img src="'.CSR_PICS.'forum/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" /></a> ';
				}
				//bewerken als bericht van gebruiker is, of als men mod is.
				if($this->_forum->magBewerken($aBericht['postID'])){
					echo '<a href="/forum/bewerken/'.$aBericht['postID'].'">
						<img src="'.CSR_PICS.'forum/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" /></a> ';
				}
				//verwijderlinkje, niet als er maar een bericht in het onderwerp is.
				if($this->_forum->isModerator()){
					echo '<a href="/forum/verwijder-bericht/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u deze post wilt verwijderen?\')">';
					echo '<img src="'.CSR_PICS.'forum/verwijderen.png" title="Verwijder bericht" alt="Verwijder bericht" style="border: 0px;" /></a>';
				}
				//goedkeuren van berichten
				if($this->_forum->isModerator() AND $aBericht['zichtbaar']=='wacht_goedkeuring'){
					echo '<br /><a href="/forum/keur-goed/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u dit bericht wilt goedkeuren?\')">bericht goedkeuren</a>';
					echo '<br /><a href="/tools/stats.php?ip='.$aBericht['ip'].'">ip-log</a>';
				}
				echo '</td>';
				
				//het eigenlijke bericht weergeven.
				echo "\r\n".'<td class="forumbericht'.($iWissel%2).'">';
				$sBericht=$aBericht['tekst'];
				$ubb = new csrUbb();
				$sBericht=$ubb->getHTML($sBericht);
				echo $sBericht.'</td></tr>';
				//tussenlijntje
				echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				$iWissel++;
			}//einde foreach $aBerichten
			//nu nog ff een quickpost formuliertje
			echo '<tr><td class="forumauteur">';
			if($this->getCiteerPost()==0){
				echo '<a class="forumpostlink" id="laatste">Snel reageren:</a><br /><br />';
				$iTekstareaRegels=6;
			}else{
				echo '<a class="forumpostlink" id="laatste"><stong>Citeren:</strong></a><br /><br />';
				$iTekstareaRegels=20;
			}
			if($this->_forum->magBerichtToevoegen($this->_forum->getID(), $this->_forum->isOpen(), $this->_forum->getRechtenPost())){	
				// link om het tekst-vak groter te maken.
				echo '<a href="#laatste" onclick="vergrootTextarea(\'forumBericht\', 10)" title="Vergroot het invoerveld">
					invoerveld vergroten&nbsp;&raquo;</a><br /><br />';
				$ubb->viewUbbhelp();
			}			
			//berichtje weergeven  voor moderators als het topic gesloten is.
			if($this->_forum->isModerator() AND !$this->_forum->isOpen()){
				echo '<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>';
			}
			echo '</td><td class="forumtekst">';
			if($this->_forum->magPosten()){ 
				echo '<form method="post" action="/forum/toevoegen/'.$this->_forum->getID().'"><p>';
				//berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden.
				if(!$this->_forum->isIngelogged()){
					echo '<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/groepen/commissie/PubCie.html">PubCie</a>. Het vermelden van <em>uw naam</em> verhoogt de kans dat dit gebeurt.</strong><br /><br />';
				}
				echo '<textarea name="bericht" id="forumBericht" class="tekst" rows="'.$iTekstareaRegels.'" cols="80" style="width: 100%;" >';
				//inhoud van de textarea vullen met eventuele quote...
				if($this->getCiteerPost()!=0){
					$aPost=$this->_forum->getSinglePost($this->getCiteerPost());
					echo '[citaat='.$aPost['uid'].']'.$aPost['tekst'].'[/citaat]';
				}
				echo '</textarea><br /><input type="submit" name="submit" value="opslaan" /></p></form>';
			}else{
				if($aBericht['open']==1){
					//wel open, geen rechten.
					echo 'U mag in dit deel van het forum niet reageren.';
				}else{
					//gesloten, wel rechten
					echo 'U kunt hier niet meer reageren omdat dit onderwerp gesloten is';
				}
			}
			echo '</td></tr></table>';
			//linkjes voor het forum nogeens weergeven, maar alleen als het aantal berichten in het onderwerp groter is dan 4
			if($this->_forum->getSize()>4){ 
			//navigatielinks voor in het forum weergeven:
			echo '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
				<a href="/forum/categorie/'.$this->_forum->getCatID().'" class="forumGrootlink">
					'.mb_htmlentities($this->_forum->getCatTitel()).'
				</a> &raquo; 
				'.mb_htmlentities($this->_forum->getTitel()).'</h2>';
			}
		}
	}
}
?>
