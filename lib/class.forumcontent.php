<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.forumcontent.php
# -------------------------------------------------------------------
# Historie:
#	03-03-2006 Jieter
# . basis functionaliteit is af.
# 28-01-2006 Jieter
# . gemaakt
#
require_once('bbcode/include.bbcode.php');

class ForumContent extends SimpleHTML {
	var $_forum;
	var $_actie;
	
	var $_sError=false;
	var $_topicsPerPagina;
	
	function ForumContent($bForum, $actie){
		$this->_forum=$bForum;
		$this->_actie=$actie;
		$this->_topicsPerPagina=$bForum->_topicsPerPagina;
	}
/***********************************************************************************************************
* Overzicht van Categorieën met aantal topics en posts
*
***********************************************************************************************************/	
	function viewCategories(){
		$aCategories=$this->_forum->getCategories();
		echo '<h2>Forum</h2>';
		//eventuele foutmelding weergeven:
		echo $this->getError();
		echo '<table class="forumtabel">
			<tr>
				<td class="forumhoofd">Forum</td>
				<td class="forumhoofd">onderwerpen</td>
				<td class="forumhoofd">berichten</td>
				<td class="forumhoofd">verandering</td>
			</tr>';
		if(is_array($aCategories)){
			foreach($aCategories as $aCategorie){
				if($this->_forum->_lid->hasPermission($aCategorie['rechten_read'])){
					echo '
<tr>
<td class="forumtitel">
	<a href="/forum/categorie/'.$aCategorie['id'].'">'.mb_htmlentities($aCategorie['titel']).'</a><br />
	'.$aCategorie['beschrijving'].'
</td>
<td class="forumreacties">'.$aCategorie['topics'].'</td>
<td class="forumreacties">'.$aCategorie['reacties'].'</td>
<td class="forumreactiemoment">';
					if($aCategorie['lastpost']!='0000-00-00 00:00:00'){
						//als de dag vandaag is, niet de datum weergeven maar 'vandaag'
						if(date('Y-m-d')==substr($aCategorie['lastpost'], 0, 10)){
							echo 'Vandaag om '.date("G:i", strtotime($aCategorie['lastpost']));;
						}else{
							echo date("G:i j-n-Y", strtotime($aCategorie['lastpost']));
						}
						echo '<br /><a href="/forum/onderwerp/'.$aCategorie['lasttopic'].'#'.$aCategorie['lastpostID'].'">reactie</a> door ';
						if(trim($aCategorie['lastuser'])!=''){
							$sUsername=$this->_forum->getForumNaam($aCategorie['lastuser']);
							if(trim($sUsername!='')){
								echo '<a href="/leden/profiel/'.$aCategorie['lastuser'].'">'.mb_htmlentities($sUsername).'</a>';
							}else{
								echo 'onbekend';
							}
						}else{
							echo 'onbekend';
						}
					}else{
						echo 'nog geen berichten';
					}
					echo '</td></tr>';
				}else{
					//deze categorie mag gebruiker niet zien. Niets weergeven dus.
				}
			}//einde foreach
		}else{
			echo '<tr><td colspan="4">Er zijn nog geen categorie&euml;n of er is iets mis met het databeest</td></tr>';
		}
		echo '</table>';
	}
/***********************************************************************************************************
*	Topics laten zien in een categorie
*
***********************************************************************************************************/	
	function viewTopics($iCat){
		$iCat=(int)$iCat;
		//controleer of de categorie wel bestaat
		if($this->_forum->catExistsVoorUser($iCat)){
			$sCategorie=$this->_forum->getCategorieTitel($iCat);
			//topics ophaelen voor deze categorie
			//wellicht wel ander pagina?
			if(isset($_GET['pagina'])){ $iPaginaID=(int)$_GET['pagina']; }else{ $iPaginaID=0; }
			$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			
			//weergeven van de navigatielinks:
			$sNavigatieLinks='<h2><a class="forumGrootlink" href="/forum/">Forum</a> &raquo; '.mb_htmlentities($sCategorie).'</h2>';
			echo $sNavigatieLinks;
			//eventuele foutmelding weergeven:
			echo $this->getError();
			echo '<table class="forumtabel"><tr>';
			$iKolommen=4;
			if($this->_forum->_lid->hasPermission('P_FORUM_MOD')){
				echo '<td class="forumhoofd">&nbsp;</td>';
				$iKolommen=5;
			}
			echo '<td class="forumhoofd">Titel</td><td class="forumhoofd">Reacties</td>';
			echo '<td class="forumhoofd">Auteur</td><td class="forumhoofd">verandering</td></tr>';
			if(is_array($aTopics)){
				//aantal topics tellen:
				$iAantalTopics=$this->_forum->topicCount($iCat);
				foreach($aTopics as $aTopic){
					echo "\r\n".'<!--begin onderwerp regel--><tr>';
					//vakje met modereerdingen
					if($this->_forum->_lid->hasPermission('P_FORUM_MOD')){
						echo '<td class="forumtitel"><a href="/forum/verwijder-onderwerp/'.$aTopic['id'].'" ';
						echo 'onclick="return confirm(\'Weet u zeker dat u dit topic wilt verwijderen?\')">';
						echo '<img src="/images/verwijderen.png" style="border: 0px;" alt=" " /></a></td>'."\r\n";
					}
					//cel met topictitel
					echo '<td class="forumtitel">';
					//[peiling] ervoor voor onderschijd bij een peiling.
					if($aTopic['soort']=='T_POLL'){	echo '[peiling] '; }
					if($aTopic['zichtbaar']=='wacht_goedkeuring'){ echo '[ bevestiging nodig... ] '; }
					//topictitel met link naar de laatste post in het onderwerp
					echo '<a href="/forum/onderwerp/'.$aTopic['id']. '#laatste" >';
					//plaatje voor plakkerig tonen
					if($aTopic['plakkerig']==1){
						echo '<img src="/images/plakkerig.gif" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt=" " style="border: 0px;" />&nbsp;&nbsp;';
					}
					//plaatje voor gesloten tonen
					if($aTopic['open']==0){
						echo '<img src="/images/slotje.png" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt=" " style="border: 0px;" />&nbsp;&nbsp;';
					}
					//titel
					echo mb_htmlentities($aTopic['titel']).'</a></td> ';
		
					//aantal reacties in dit topic
					echo '<td class="forumreacties">'.($aTopic['reacties']-1).'</td>';
					//draadstarter:
					$sUsername=$this->_forum->getForumNaam($aTopic['uid']);
					echo '<td class="forumreacties"><a href="/leden/profiel/'.$aTopic['uid'].'">'.mb_htmlentities($sUsername).'</a></td>';
					//laatste veranderingen
					echo '<td class="forumreactiemoment">';
					if(date('Y-m-d')==substr($aTopic['lastpost'], 0, 10)){
						echo 'Vandaag om '.date("G:i", strtotime($aTopic['lastpost']));
					}else{
						echo date("G:i j-n-Y", strtotime($aTopic['lastpost']));
					}
					echo '<br /><a href="/forum/onderwerp/'.$aTopic['id'].'#'.$aTopic['lastpostID'].'">reactie</a> door ';
					if(trim($aTopic['lastuser'])!=''){
						$sUsername=$this->_forum->getForumNaam($aTopic['lastuser']);
						if(trim($sUsername!='')){
							echo '<a href="/leden/profiel/'.$aTopic['lastuser'].'">'.mb_htmlentities($sUsername).'</a>';
						}else{
							echo 'onbekend';
						}
					}else{
						echo 'onbekend';
					}
					echo '</td></tr><!--einde onderwerp regel-->'."\r\n";
				}
			}else{//$aTopics is geen array, dus bevat geen berichten.
				$iAantalTopics=0;
				echo '<tr><td colspan="3">Deze categorie bevat nog geen berichten of deze pagina bestaat niet.</td></tr>';
				$aTopic['rechten_post']=$this->_forum->getRechten_post($iCat);
			}
			//nieuw topic formuliertje
			//kijken of er wel gepost mag worden en of de categorie bestaat.
			echo '<tr><td colspan="'.($iKolommen-1).'" class="forumhoofd">';
			if($this->_forum->_lid->hasPermission($aTopic['rechten_post'])){
				echo 'Onderwerp Toevoegen';
			}else{
				echo '&nbsp;';
			}
			echo '</td>';
			//pagineringslinkjes.
			echo '<td class="forumhoofd">';
			if($iAantalTopics>$this->_topicsPerPagina){
				$iAantalPaginas=ceil($iAantalTopics/$this->_topicsPerPagina);
				echo 'pagina: ';
				for($iPagina=0; $iPagina<$iAantalPaginas; $iPagina++){ 
					if($iPagina==$iPaginaID){
						echo ($iPagina+1).' ';
					}else{
						echo '<a href="/forum/cat/'.$iCat.'/'.$iPagina.'">'.($iPagina+1).'</a> ';
					}
				}
			}
			echo '</td></tr>';
			if($this->_forum->_lid->hasPermission($aTopic['rechten_post'])){
				echo '
				<tr><td colspan="'.$iKolommen.'" class="forumtekst">
					<form method="post" action="/forum/onderwerp-toevoegen/'.$iCat.'">
					
					<p>';
				if($this->_forum->_lid->hasPermission('P_LOGGED_IN')){
					echo 'Hier kunt u een onderwerp toevoegen in deze categorie van het forum.<br /><br />';
				}else{
					//melding voor niet ingelogde gebruikers die toch willen posten. Ze wordeb 'gemodereerd', dat wil zeggen, de topics zijn
					//nog niet direct zichtbaar.
					echo 'Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					 &eacute;&eacute;rst door	de PubCie worden goedgekeurd. <br /><span style="text-decoration: underline;">
					 Het is hierbij verplicht om uw naam en een email-adres onder het bericht te plaatsen. Dan kan de PubCie 
					 eventueel contact met u opnemen. Doet u dat niet, dan wordt u bericht wellicht niet geplaatst!</span>
					 <br /><br />';
				}
				echo '
						<a class="forumpostlink" name="laatste"><strong>Titel</strong></a><br />
						<input type="text" name="titel" value="" class="tekst" style="width: 100%" /><br />
						<strong>Bericht</strong>&nbsp;&nbsp; ';
				// link om het tekst-vak groter te maken.
				echo '<a href="#laatste" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">
					invoerveld vergroten</a><br />';
				echo '<textarea name="bericht" id="forumBericht" rows="10" cols="80" style="width: 100%" class="tekst"></textarea><br />
						<input type="submit" name="submit" value="verzenden" />
						</p>
						</form>
						</td></tr>';
			}
			echo '</table>';
			//nog eens de navigatielinks die ook bovenaan staan.
			echo $sNavigatieLinks;
		}else{
			echo '<h2>Dit gedeelte van het forum is niet zichtbaar voor u, of het bestaat &uuml;berhaupt niet.</h2>
				<a href="/forum/">Terug naar het forum</a>';
		}
	}
/***********************************************************************************************************
* Het Topic uiteindelijk weergeven.
*
***********************************************************************************************************/	
	function viewTopic($iTopic, $iCiteerPost=0){
		$iTopic=(int)$iTopic;
		$aBerichten=$this->_forum->getPosts($iTopic);
		$rechten_post=$aBerichten[0]['rechten_post'];
		if(is_array($aBerichten) AND $this->_forum->_lid->hasPermission($aBerichten[0]['rechten_read'])){
			//navigatielinks voor in het forum weergeven:
			$sNavigatieLinks='<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
				<a href="/forum/categorie/'.$aBerichten[0]['categorie'].'" class="forumGrootlink">
					'.mb_htmlentities($this->_forum->getCategorieTitel($aBerichten[0]['categorie'])).'
				</a> &raquo; 
				'.mb_htmlentities($aBerichten[0]['titel']).'</h2>';
			echo $sNavigatieLinks;
			//eventuele foutmelding weergeven:
			echo $this->getError();
			//topic mod dingen:
			if($this->_forum->_lid->hasPermission('P_FORUM_MOD')){
				echo "\r\n".'U mag dit topic modereren:<br /> ';
				//topic verwijderen
				echo '[ <a href="/forum/verwijder-onderwerp/'.$iTopic.'" onclick="return confirm(\'Weet u zeker dat u dit topic wilt verwijderen?\')">verwijderen</a> ';
				if($aBerichten[0]['open']==1){
					echo '| <a href="/forum/sluit-onderwerp/'.$iTopic.'">sluiten (reageren niet meer mogelijk)</a> ';
				}else{
					echo '| <a href="/forum/open-onderwerp/'.$iTopic.'">weer openen (reageren weer w&eacute;l mogelijk)</a> ';
				}	
				if($aBerichten[0]['plakkerig']==0){
					echo '| <a href="/forum/maak-plakkerig/'.$iTopic.'">maak plakkerig</a> ';
				}else{
					echo '| <a href="/forum/maak-niet-plakkerig/'.$iTopic.'">verwijder plakkerigheid</a> ';
				}
				if($aBerichten[0]['zichtbaar']=='wacht_goedkeuring'){
					echo '| <a href="/forum/keur-goed/'.$iTopic.'">Keur dit bericht goed.</a> ';
				}
				echo ']<br /><br />'."\r\n";
			}
			echo '<table class="forumtabel"><tr><td class="forumhoofd">auteur</td><td class="forumhoofd">bericht</td></tr>';
			//speciale topic weergeven als het topic er een is. bijvoorbeeld een poll;
			switch($aBerichten[0]['soort']){
				case 'T_POLL':
					require_once('class.forumpoll.php');
					$poll=new ForumPoll($this->_forum);
					//dingen ophalen
					$aPollOpties=$poll->getPollOpties($iTopic);
					$iPollStemmen=$poll->getPollStemmen($iTopic);
					$iPollOpties=count($aPollOpties);
					//er mag maar één keer per *ingelloged lid* per poll gestemd worden, en alleen als het topic open is.
					if(!$this->_forum->_lid->hasPermission($rechten_post)){
						$bMagStemmen=false;
					}else{
						if($poll->uidMagStemmen($iTopic) AND ($aBerichten[0]['open']==1)){
							$bMagStemmen=true;
						}else{
							$bMagStemmen=false;
						}
					}
					//html dan maer
					echo '<tr><td class="forumauteur">Een peiling van ';
					if($aBerichten[0]['startUID']==STATISTICUS){
						echo 'am. Verenigings statisticus';
					}else{
						echo mb_htmlentities($this->_forum->getForumNaam($aBerichten[0]['startUID']));
					}
					echo ':<br /><br /><br />Er is '.$iPollStemmen.' keer gestemd.</td><td class="forumbericht0">';
					echo '<form action="/forum/stem/'.$iTopic.'" method="post" >';
					echo '<table style="width: 100%; margin: 10px 10px 10px 10px; background-color: #f1f1f1;" border="0">';
					//poll vraag nog een keer
					echo '<tr><td colspan="3"><strong>'.mb_htmlentities($aBerichten[0]['titel']).'</strong></td></tr>';
					foreach($aPollOpties as $aPollOptie){
						//lengte van de balk, en het percentage van de stemmen
						if($aPollOptie['stemmen']!=0){
							$fPercentage=(100/$iPollStemmen)*$aPollOptie['stemmen'];
							$iBalkLengte=floor($fPercentage*2); //($iPollOpties/1.5));
						}else{
							$fPercentage=$iBalkLengte=0;
						}
						echo '<tr><td>';
						//forumulier enkel tonen als er gestemd mag worden
						if($bMagStemmen){
							echo '<input type="radio" name="pollOptie" id="'.$aPollOptie['id'].'" value="'.$aPollOptie['id'].'" />';
						}
						echo '<label for="'.$aPollOptie['id'].'">'.mb_htmlentities($aPollOptie['optie']).'</label></td>';
						echo '<td><img src="/images/frikandel.png" height="20px" width="'.$iBalkLengte.'px" title="een del, lekker!" /></td>';
						echo '<td style="width: 80px">'.round( $fPercentage, 2).'% ('.$aPollOptie['stemmen'].')</td></tr>';
					}
					//verzendknopje enkel tonen als er gestemd mag worden
					if($bMagStemmen){
						echo '<tr><td colspan="3"><input type="submit" value="stemmen" name="stemmen" /> <em>(Als u hier klikt wordt uw eventuele commentaar niet opgeslagen)</em></td></tr>';
					}
					echo '</table></form>';
					echo '</td></tr>';
					//tussenlijntje
					echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				break;
				case 'T_LEZING':
				break;
				case 'T_STANDAARD':
				default:
				break;
			}
			
			//berichten in dit topic tellen, kan ook uit getPosts gehaald worden eventueel,
			//moet er dan nog ingeklust worden.
			$iBerichtenAantal=count($aBerichten);
			$iWissel=1;
			foreach($aBerichten as $aBericht){
				echo '<tr><td class="forumauteur">';
				$sUsername=$this->_forum->getForumNaam($aBericht['uid'], $aBericht);
				if(trim($sUsername!='')){
					echo '<a href="/leden/profiel/'.$aBericht['uid'].'">'.mb_htmlentities($sUsername).'</a> schreef ';
				}else{
					echo 'onbekend';
				}
				//anker maken met post-ID
				echo '<a class="forumpostlink" name="'.$aBericht['postID'].'">';
				if(date('Y-m-d')==substr($aBericht['datum'], 0, 10)){
					echo 'om '.date("G:i", strtotime($aBericht['datum']));;
				}else{
					echo 'op '.date("j-n-Y \o\m G:i", strtotime($aBericht['datum']));
				}
				echo '</a>';
				if($aBericht['bewerkDatum']!='0000-00-00 00:00:00'){
					echo '<br />Bewerkt op '.date("j-n-Y \o\m G:i", strtotime($aBericht['bewerkDatum'])).'';
				}
				echo '<br />';
				//citeer knop enkel als het topic open is en als men mag posten, of als men mod is.
				if(($aBericht['open']==1 AND $this->_forum->_lid->hasPermission($rechten_post)) OR 
					$this->_forum->_lid->hasPermission('P_FORUM_MOD')){
					echo ' <a href="/forum/reactie/'.$aBericht['postID'].'#laatste"><img src="/images/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" /></a> ';
				}
				//bewerken als bericht van gebruiker is, of als men mod is.
				if($this->_forum->magBewerken($aBericht['postID'], $aBericht['uid'], $aBericht['open'], $rechten_post)){
					echo '<a href="/forum/bewerken/'.$aBericht['postID'].'">
						<img src="/images/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" /></a> ';
				}
				//verwijderlinkje, niet als er maar een bericht in het onderwerp is.
				if($iBerichtenAantal!=1 AND $this->_forum->_lid->hasPermission('P_FORUM_MOD')){
					echo '<a href="/forum/verwijder-bericht/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u deze post wilt verwijderen?\')">';
					echo '<img src="/images/verwijderen.png" title="Verwijder bericht" alt=" " style="border: 0px;" /></a>';
				}
				echo '</td>';
				
				//het eigenlijke bericht weergeven.
				echo "\r\n".'<td class="forumbericht'.($iWissel%2).'">'.bbview($aBericht['tekst'], $aBericht['bbcode_uid']).'</td></tr>';
				//tussenlijntje
				echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				$iWissel++;
			}//einde foreach $aBerichten
			//nu nog ff een quickpost formuliertje
			echo '<tr><td class="forumauteur">';
			if($iCiteerPost==0){
				echo '<a class="forumpostlink" name="laatste">Snel reageren:</a><br /><br />';
				$iTekstareaRegels=6;
			}else{
				echo '<a class="forumpostlink" name="laatste"><stong>Citeren:</strong></a><br /><br />';
				$iTekstareaRegels=20;
			}
			if($this->_forum->magBerichtToevoegen($iTopic, $aBericht['open'], 'P_FORUM_POST')){	
				// link om het tekst-vak groter te maken.
				echo '<a href="#laatste" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">
					invoerveld vergroten &raquo;</a><br />';
			}			
			//berichtje weergeven  voor moderators als het topic gesloten is.
			if($this->_forum->_lid->hasPermission('P_FORUM_MOD') AND $aBericht['open']==0){
				echo '<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>';
			}
			echo '</td><td class="forumtekst">';
			//if($this->_forum->magBerichtToevoegen($iTopic, $aBericht['open'], 'P_FORUM_POST')){	
			if($this->_forum->magBerichtToevoegen($iTopic, $aBericht['open'], $rechten_post)){ 
			//^ nu werkt dit nog niet, omdat htdocs/forum/toevoegen.php er nog niet mee kan omgaan.
				echo '<form method="post" action="/forum/toevoegen/'.$iTopic.'"><p>';
				echo '<textarea name="bericht" id="forumBericht" class="tekst" rows="'.$iTekstareaRegels.'" cols="80" style="width: 100%;" >';
				//inhoud van de textarea vullen met eventuele quote...
				if($iCiteerPost!=0){
					$aPost=$this->_forum->getPost((int)$iCiteerPost);
					$sCiteerBericht=bbedit($aPost['tekst'], $aPost['bbcode_uid']);
					echo '[quote]'.$sCiteerBericht.'[/quote]';
				}
				echo '</textarea><br />';
				echo '<input type="submit" name="submit" value="opslaan" /></p></form>';
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
			if($iBerichtenAantal>4){
				echo $sNavigatieLinks;
			}
		}else{
			if(!is_array($aBerichten)){
				echo 'Onderwerp bestaat helaas niet (meer).';
			}else{
				echo '<h3>helaas</h3>Dit gedeelte van het forum is niet beschikbaar voor u, u zult moeten inloggen, of terug gaan 
					naar <a href="/forum/">het forum</a>';
			}
		}
	}
/***********************************************************************************************************
* Een topic Toevoegen. deze wordt niet gebruikt tot nu toe
*
***********************************************************************************************************/	
	function toevoegFormulier($iTopic=0){
		$sTopicTitel=$this->_forum->getTopicTitel($iTopicID);
		if($iTopic==0 OR $sTopicTitel!==false){
			//nu mag er wat weergegeven worden
			echo '<h2>Topic toevoegen</h2>';
			echo '<form method="post" action="/forum/toevoegen/'.$iTopic.'" >';
			if($iTopic==0){
				//nieuw topic, dus titel vragen.
				echo '<strong>Titel</strong><br />Geeft u a.u.b. een titel op die de lading ook dekt.<br /><input type="text" name="titel" value="" />';
			}else{
				//oud topic, titel weergeven.
				echo 'titel:<br />'.$sTopicTitel.'<br />'; 
			}	
			
			echo '</form>';
		}else{
			//feutmelding
		}
	}
/***********************************************************************************************************
* Een bericht citeren.
*
***********************************************************************************************************/	
	function citeerFormulier($iPostID){
		$iPostID=(int)$iPostID;
		$aPost=$this->_forum->getPost($iPostID);
		if(is_array($aPost)){
			$iTopicID=$this->_forum->getTopicVoorPostID($iPostID);
			$sBericht=bbedit($aPost['tekst'], $aPost['bbcode_uid']);
			//$sBericht=preg_replace('/\[quote\].*(\[quote\].*\[\/quote\]).*\[\/quote\]/', '', $sBericht);
			//navigatielinks
			$sNavigatieLinks='<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
				<a href="/forum/categorie/'.$aPost['categorieID'].'" class="forumGrootlink">
					'.mb_htmlentities($aPost['categorieTitel']).'
				</a> &raquo; <a href="/forum/onderwerp/'.$iTopicID.'#'.$iPostID.'" class="forumGrootlink">
				'.mb_htmlentities($aPost['topicTitel']).'</a> &raquo; bericht citeren</h2>';
			echo $sNavigatieLinks;
			echo '<table class="forumtabel">
				<tr><td colspan="3" class="forumhoofd">Bericht toevoegen</td><td class="forumhoofd"></td></tr>
				<tr><td colspan="4" class="forumtekst">
				<form method="post" action="/forum/toevoegen/'.$iTopicID.'">
				<strong>Bericht</strong>&nbsp;&nbsp;';
			// link om het tekst-vak groter te maken.
			echo '<a href="#" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';
			echo '<br />
				<textarea name="bericht" id="forumBericht" rows="20" style="width: 100%" class="tekst">[quote]'.$sBericht.'[/quote]</textarea><br />
				<input type="submit" name="submit" value="verzenden" /> <a href="/forum/onderwerp/'.$iTopicID.'#laatste">terug naar onderwerp</a>
				</form>
				</td></tr></table>';
		}else{
			echo '<h2>De post waarop u probeert te reageren bestaat niet.</h2><a href="/forum/">Terug naar het forum</a>';
		}
		
	}
/***********************************************************************************************************
* een bericht bewerken.
*
***********************************************************************************************************/	
	function bewerkFormulier($iPostID){
		$iPostID=(int)$iPostID;
		if($this->_forum->magBewerken($iPostID)){
			$iTopicID=$this->_forum->getTopicVoorPostID($iPostID);
			if($iTopicID!=0 OR !preg_match("/^(\d*)$/", $iTopicID)){
				$sTopicTitel=$this->_forum->getTopicTitel($iTopicID);
				$aPost=$this->_forum->getPost($iPostID);
				//navigatielinks
				$sNavigatieLinks='<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
					<a href="/forum/categorie/'.$aPost['categorieID'].'" class="forumGrootlink">
						'.mb_htmlentities($aPost['categorieTitel']).'
					</a> &raquo; <a href="/forum/onderwerp/'.$iTopicID.'#'.$iPostID.'" class="forumGrootlink">
					'.mb_htmlentities($aPost['topicTitel']).'</a> &raquo; bericht bewerken</h2>';
				echo $sNavigatieLinks;
				
				echo '<table class="forumtabel">
					<tr><td colspan="3" class="forumhoofd">Bericht bewerken</td><td class="forumhoofd">&nbsp;</td></tr>
					<tr><td colspan="4" class="forumtekst">
					<form method="post" action="/forum/bewerken/'.$iPostID.'">
					<h3>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Gebruik bijvoorbeeld [s]...[/s]</h3>
					<strong>Bericht</strong>&nbsp;&nbsp;';
				// link om het tekst-vak groter te maken.
				echo '<a href="#" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">invoerveld vergroten</a><br />';

				echo '
					<textarea name="bericht" id="forumBericht" rows="20" style="width: 100%" class="tekst">'.
						bbedit($aPost['tekst'], $aPost['bbcode_uid']).'</textarea><br />
					<input type="submit" name="submit" value="verzenden" /> <a href="/forum/onderwerp/'.$iTopicID.'#laatste">terug naar onderwerp</a>
					</form>
					</td></tr></table>';
			}else{
				echo '<h2>Dit bericht bestaat niet.</h2>
					Terug naar <a href="/forum/">het forum.</a>';
			}
		}else{
			$iTopicID=$this->_forum->getTopicVoorPostID($iPostID);
			echo '<h2>Dit bericht mag u niet bewerken.</h2>
				Terug naar <a href="/forum/onderwerp/'.$iTopicID.'">Vergeet bewerken, ga terug naar het onderwerp waar u vandaan kwam.</a>';
		}
	}
/***********************************************************************************************************
* poll toevoegen
*
***********************************************************************************************************/	
	function pollFormulier($iCatID){
		$sTitel=$sBericht='';
		if(isset($_POST['titel'])){
			$sTitel=trim($_POST['titel']);
		}
		if(isset($_POST['bericht'])){
			$sBericht=trim($_POST['bericht']);
		}
		
		echo '<form action="/forum/maak-stemming/'.$iCatID.'" method="post"><table class="forumtabel">
					<tr><td colspan="3" class="forumhoofd">Peiling toevoegen</td><td class="forumhoofd"></td></tr>
					<tr><td colspan="4" class="forumtekst">';
		if($this->_sError!==false){
			echo '<div class="foutmelding">'.$this->_sError.'</div>';
		}
		?>
<strong>Vraag/stelling</strong><br />
<input type="text" name="titel" value="<?php echo $sTitel; ?> " style="width: 100%" class="tekst" /><br />
</td></tr>
<tr><td colspan="4" class="forumtekst">
<strong>Opties voor de peiling:</strong><br />
Lege velden worden genegeerd.<br /><br />
		<?php
		for($iTeller=0; $iTeller<6; $iTeller++){
			if(isset($_POST['opties'][$iTeller]) AND trim($_POST['opties'][$iTeller])!=''){
				$sOptie=trim($_POST['opties'][$iTeller]);
			}else{
				$sOptie='';
			}
			echo ($iTeller+1).'. <input type="text" name="opties[]" value="'.$sOptie.'" style="width: 70%" class="tekst" /><br />';
		}
		echo '</td></tr>
					<tr><td colspan="4" class="forumtekst">
						<strong>Bericht</strong><br />
						<textarea name="bericht" rows="10" style="width: 100%" class="tekst">'.$sBericht.'</textarea><br />			
						<input type="submit" name="submit" value="verzenden" /> <a href="/forum/categorie/'.$iCatID.'">terug naar categorie</a>
					</td></tr>
					
		</table></form>';
	}
/***********************************************************************************************************
* rss feed weergeven van het forum.
*
***********************************************************************************************************/
	function rssFeed(){
		$aPosts=$this->_forum->getLastPosts();
		$datum=date('r');
		//hoofder maeken
		?>
<rss version="2.0">
	<channel>
		<copyright>Copyright 2006 C.S.R.-Delft</copyright>
		<pubDate><?php echo $datum; ?></pubDate>
		<lastBuildDate><?php echo $datum; ?></lastBuildDate>
		<docs>http://csrdelft.nl/leden/index.php</docs>
		<description>
			C.S.R.-Delft: Vereniging van Christen-studenten te Delft.
		</description>
		<image>
			<link>http://csrdelft.nl/</link>
			<title>C.S.R.-Delft</title>
			<url>http://csrdelft.nl/informatie/images/csr.jpg</url>
			<height>150</height>
			<width>118</width>
			<description>Logo van C.S.R.-Delft</description>
		</image>
		<language>nl-nl</language>
		<link>http://csrdelft.nl/forum/</link>
		<title>C.S.R.-Delft forum laatste berichten.</title>
		<managingEditor>PubCie</managingEditor>
		<webMaster>pubcie@csrdelft.nl</webMaster>
<?php
		foreach($aPosts as $aPost){
			//datum naar rfc 822 rossen
			$pubDate=date('r', strtotime($aPost['datum']));
			//ff al de ubb kek eruit rossen...
			$bbcode_uid=$aPost['bbcode_uid'];
			//[b][/b]
				$tekst=preg_replace('/\[b:'.$bbcode_uid.'\](.*?)\[\/b:'.$bbcode_uid.'\]/', '*\\1*', $aPost['tekst']);
			//alle andere ubb kek eruit rossen...
			$tekst=preg_replace('/(\[(|\/)\w+:[a-f0-9]+\])/', '|', $tekst);
			//$volledigetekst=$tekst=preg_replace('/(\[(|\/)url=http://[a-f0-9]+:[a-f0-9]+\])/', '|', $volledigetekst);
			$volledigetekst=$tekst;
			if(kapStringNetjesAf($tekst, 50)){
				$tekst.='...';
			}
			echo '<item>';
			echo '<title>'.$aPost['nickname'].': '.str_replace(array("\r\n", "\r", "\n"), ' ', $tekst).'</title>';
			echo '<link>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'#'.$aPost['postID'].'</link>';
			
			echo '<description>'.$volledigetekst.'</description>';
			echo '<author>'.$this->_forum->getForumNaam($aPost['uid'], $aPost).'</author>';
			echo '<category>forum: '.$aPost['titel'].'</category>';
			echo '<comments>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'#laatste</comments>';
			echo '<guid>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'#'.$aPost['postID'].'</guid>';
			echo '<pubDate>'.$pubDate.'</pubDate>';
			echo '</item>';
		}
		echo '</channel>';
		echo '</rss>';
	}
	function setError($sError){
		$this->_sError=$sError;
	}
	function getError(){
		if(isset($_GET['fout'])){
			return '<div class="foutmelding">'.mb_htmlentities(base64_decode(trim($_GET['fout']))).'</div>';
		}
	}
	function view(){
		switch($this->_actie){
			case 'forum': if(isset($_GET['forum'])){ $this->viewTopics((int)$_GET['forum']); }else{ $this->viewCategories(); } break;
			case 'topic': $this->viewTopic((int)$_GET['topic']); break;
			case 'nieuw-poll':
				if(isset($_GET['cat']) AND $this->_forum->catExists($_GET['cat'])){
					$iCatID=(int)$_GET['cat'];
				}else{
					//standaard worden stemmingen in de categorie daarvoor gerost.
					$iCatID=7;
				}	
				$this->pollFormulier($iCatID);
			break;
			case 'bewerk': if(isset($_GET['post'])){ $this->bewerkFormulier((int)$_GET['post']); }else{ $this->viewCategories(); } break;
			case 'citeren': 
				if(isset($_GET['post'])){
					$this->viewTopic($this->_forum->getTopicVoorPostID((int)$_GET['post']), (int)$_GET['post']); 
				}else{ 
					$this->viewCategories(); 
				} 
			break;
			case 'rss': $this->rssFeed();	break;
			default: $this->viewCategories();	break;
		}
		if($this->_forum->_lid->hasPermission('P_FORUM_MOD') AND $this->_actie!='rss'){
			echo '<br />forum parsetijd: '.round($this->_forum->getParseTime(), 4).' seconden';
		}
	}
}
?>
