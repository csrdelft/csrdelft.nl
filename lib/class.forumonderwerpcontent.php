<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerpcontent.php
# -------------------------------------------------------------------


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

	
	public function citeer($iPostID){
		//TODO: check of deze post wel bestaat, anders niets citeren.
		$this->citeerPost=(int)$iPostID;
	}
		
	private function getCiteerPost(){
		return $this->citeerPost;
		
	}
	function viewWaarbenik(){
		$sTitel='<a href="/communicatie/forum/">Forum</a>'.
			' &raquo; <a href="/communicatie/forum/categorie/'.$this->_forum->getCatID().'">'.$this->_forum->getCatTitel().'</a>';
		$topicTitel=$this->_forum->getTitel();
		
		if(strlen($topicTitel)>70){ $topicTitel=substr($topicTitel, 0, 68).'...'; }
		$sTitel.=' &raquo; '.$topicTitel.'';
		echo $sTitel;
	}
	function getTitel(){
		$sTitel='Forum - '.
			$this->_forum->getCatTitel().' - '.
			$this->_forum->getTitel();
		return $sTitel;
	}
	function view(){
		if($this->_forum->getPosts()===false){
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>';
			echo 'Dit gedeelte van het forum is niet beschikbaar voor u, u zult moeten inloggen, of terug gaan naar <a href="/communicatie/forum/">het forum</a>';
			if($this->_forum->isModerator()){ 
				echo '<h2>Debuginformatie</h2><pre>'.print_r($this, true).'</pre>'; 
			}

		}else{
			$smarty=new Smarty_csr();
			$smarty->assign('forum', $this->_forum);
			$smarty->assign('melding', $this->getMelding());
			if($this->_forum->getSoort()=='T_POLL'){
				require_once('class.forumpoll.php');
				require_once('class.pollcontent.php');
				$peiling=new ForumPoll($this->_forum);
				$peilingContent=new PollContent($peiling);
				$smarty->assign('peiling', $peilingContent);
			}
			
			//eventueel een voorbeeld voor een bericht laten zien.
			if(isset($_POST['bericht'], $_POST['submit']) AND $_POST['submit']=='voorbeeld'){
				$smarty->assign('postvoorbeeld', $_POST['bericht']);
			}
			//wat komt er in de textarea te staan?
			if($this->getCiteerPost()!=0){
				$aPost=$this->_forum->getSinglePost($this->getCiteerPost());
				$textarea='[citaat='.$aPost['uid'].']'.htmlspecialchars($aPost['tekst']).'[/citaat]';
			}elseif(isset($_POST['bericht'])){
				$textarea=htmlspecialchars($_POST['bericht']);
			}else{
				$textarea='';
			}
			$smarty->assign('textarea', $textarea);
			$smarty->assign('citeerPost', $this->getCiteerPost());
				
			$smarty->display('forum/onderwerp.tpl');
		}
		if(false){
			
		echo '<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value=\'\';" /></form>';
			$navlinks='<div class="forumNavigatie"><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; 
				<a href="/communicatie/forum/categorie/'.$this->_forum->getCatID().'" class="forumGrootlink">
					'.mb_htmlentities($this->_forum->getCatTitel()).'</a><br />
				 <h1>'.mb_htmlentities(wordwrap($this->_forum->getTitel(), 80, "\n", true)).'</h1></div>';
			echo $navlinks;
			//eventuele foutmelding weergeven:
			echo $this->getMelding();
			//topic mod dingen:
			if($this->_forum->isModerator()){
				echo "\r\n".'<fieldset id="modereren">';
				echo '<legend>Modereren</legend>';
				//topic verwijderen
				echo '<div style="float: left; width: 30%;">';
				echo '<a href="/communicatie/forum/verwijder-onderwerp/'.$this->_forum->getID().'" onclick="return confirm(\'Weet u zeker dat u dit topic wilt verwijderen?\')" class="knop"><img src="'.CSR_PICS.'forum/verwijderen.png" alt="verwijderen" /> verwijderen</a> <br /><br />';
				if($this->_forum->isOpen()){
					$opensluit='sluiten (geen reactie mogelijk)';
				}else{
					$opensluit='weer openen (reactie mogelijk)';
				}
				echo ' <a href="/communicatie/forum/openheid/'.$this->_forum->getID().'" class="knop"><img src="'.CSR_PICS.'forum/slotje.png" alt="Slotje" /> '.$opensluit.'</a><br /><br />';
				
				if($this->_forum->isPlakkerig()){
					$plakkerigheid='verwijder plakkerigheid';
				}else{
					$plakkerigheid='maak plakkerig';
				}
				echo ' <a href="/communicatie/forum/plakkerigheid/'.$this->_forum->getID().'" class="knop"><img src="'.CSR_PICS.'forum/plakkerig.gif" alt="plakkerig" /> '.$plakkerigheid.'</a>';
				echo '</div>';
				echo '<div style="float: right; width: 60%;">';
				//verplaatsen
				echo '<form action="/communicatie/forum/verplaats/'.$this->_forum->getID().'/" method="post">';
				echo '<div>Verplaats naar: <br /> <select name="newCat">';
				echo '<option value="ongeldig">... selecteer</option><optgroup>';
				foreach($this->_forum->getCategories() as $cat){
					if($cat['titel']=='SEPARATOR'){
						echo '</optgroup><optgroup label="'.str_repeat('-', 40).'">';
					}else{
						if($cat['id']!=$this->_forum->getCatID()){
							echo '</optgroup><option value="'.$cat['id'].'">'.mb_htmlentities($cat['titel']).'</option>';
						}
					}
				}
				echo '</select> <input type="submit" value="opslaan" /></div></form>';
				//titel aanpassen.
				echo '<form action="/communicatie/forum/onderwerp/hernoem/'.$this->_forum->getID().'/" method="post"><div>';
				echo 'Titel aanpassen: <br /><input type="text" name="titel" value="'.htmlspecialchars($this->_forum->getTitel()).'" style="width: 250px;" />';
				echo ' <input type="submit" value="opslaan" /></div></form>';
				echo '</div>';
				echo '</fieldset>'."\r\n";
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
			$ubb = new csrUbb();
			foreach($this->_forum->getPosts() as $aBericht){
				echo '<tr><td class="forumauteur">';
				echo $this->_forum->getForumNaam($aBericht['uid'], $aBericht).' schreef ';
				echo $this->_forum->formatDatum($aBericht['datum']);
				if($aBericht['bewerkDatum']!='0000-00-00 00:00:00'){
					echo ';<br />Bewerkt '.$this->_forum->formatDatum($aBericht['bewerkDatum']);
				}
				echo '<br />';
				//citeer knop enkel als het topic open is en als men mag posten, of als men mod is.
				if($this->_forum->magCiteren()){
					echo ' <a href="/communicatie/forum/reactie/'.$aBericht['postID'].'#laatste"><img src="'.CSR_PICS.'forum/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" /></a> ';
				}
				//bewerken als bericht van gebruiker is, of als men mod is.
				if($this->_forum->magBewerken($aBericht['postID'])){
					echo '<a onclick="forumEdit('.$aBericht['postID'].')">
						<img src="'.CSR_PICS.'forum/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" /></a> ';
				}
				//verwijderlinkje, niet als er maar een bericht in het onderwerp is.
				if($this->_forum->isModerator()){
					echo '<a href="/communicatie/forum/verwijder-bericht/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u deze post wilt verwijderen?\')">';
					echo '<img src="'.CSR_PICS.'forum/verwijderen.png" title="Verwijder bericht" alt="Verwijder bericht" style="border: 0px;" /></a>';
				}
				//goedkeuren van berichten
				if($this->_forum->isModerator() AND $aBericht['zichtbaar']=='wacht_goedkeuring'){
					echo '<br /><a href="/communicatie/forum/keur-goed/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u dit bericht wilt goedkeuren?\')">bericht goedkeuren</a>';
					echo '<br /><a href="/tools/stats.php?ip='.$aBericht['ip'].'">ip-log</a>';
				}
				echo '</td>';
				
				//het eigenlijke bericht weergeven.
				echo "\r\n".'<td class="forumbericht'.($iWissel%2).'" id="post'.$aBericht['postID'].'">'; 
				echo $ubb->getHTML($aBericht['tekst']);
				echo '</td></tr>';
				//tussenlijntje
				echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				$iWissel++;
			}//einde foreach $aBerichten
			
			//eventueele voorbeeld van een post
			if(isset($_POST['bericht'], $_POST['submit']) AND $_POST['submit']=='voorbeeld'){
				echo '<tr><td class="forumauteur">Voorbeeld van uw bericht:<br /><br />' .
						'<h4>LET OP: uw bericht is nog niet opgeslagen!</td>';
				echo '<td class="forumbericht'.($iWissel%2).'">';
				echo $ubb->getHTML($_POST['bericht']);
				echo '</td></tr>';
				echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
			}
			
			//Formulier om een bericht achter te laten
			echo '<tr><td class="forumauteur">';
			if($this->getCiteerPost()==0){
				echo '<a class="forumpostlink" id="laatste">Reageren:</a><br /><br />';
				$iTekstareaRegels=6;
			}else{
				echo '<a class="forumpostlink" id="laatste"><stong>Citeren:</strong></a><br /><br />';
				$iTekstareaRegels=20;
			}
			if($this->_forum->magToevoegen()){	
				// link om het tekst-vak groter te maken.
				echo '<a href="#laatste" onclick="vergrootTextarea(\'forumBericht\', 10)" title="Vergroot het invoerveld">
					Invoerveld vergroten&nbsp;&raquo;</a><br /><br />';
				$ubb->viewUbbhelp();
			}			
			//berichtje weergeven  voor moderators als het topic gesloten is.
			if($this->_forum->isModerator() AND !$this->_forum->isOpen()){
				echo '<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>';
			}
			echo '</td><td class="forumtekst">';
			if($this->_forum->magToevoegen()){ 
				echo '<form method="post" action="/communicatie/forum/toevoegen/'.$this->_forum->getID().'#laatste"><p>';
				//berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden.
				if(!$this->_forum->isIngelogged()){
					echo '<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>. 
						Het vermelden van <em>uw naam en email-adres</em> is verplicht.</strong><br /><br />';
				}
				echo '<textarea name="bericht" id="forumBericht" class="tekst" rows="'.$iTekstareaRegels.'" cols="80" style="width: 100%;" >';
				//inhoud van de textarea vullen met eventuele quote...
				if($this->getCiteerPost()!=0){
					$aPost=$this->_forum->getSinglePost($this->getCiteerPost());
					echo '[citaat='.$aPost['uid'].']'.htmlspecialchars($aPost['tekst']).'[/citaat]';
				}elseif(isset($_POST['bericht'])){
					echo htmlspecialchars($_POST['bericht']);
				}
				echo '</textarea><br /><input type="submit" name="submit" value="opslaan" id="forumOpslaan" /> ';
				echo '<input type="submit" name="submit" value="voorbeeld" style="color: #777;" id="forumVoorbeeld" /></p></form>';
			}else{
				if($this->_forum->isOpen()){
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
				echo $navlinks;
			}
		}
	}
}
?>
