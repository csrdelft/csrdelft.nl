<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumcontent.php
# -------------------------------------------------------------------


require_once('bbcode/include.bbcode.php');
require_once('class.simplehtml.php');

class ForumContent extends SimpleHTML {
	var $_forum;
	var $_actie;
	var $_sTitel='forum';
	
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
		//echo '<h2>Forum</h2>';
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
				//controleren of de gebruiker de huidige categorie mag zien
				if($this->_forum->_lid->hasPermission($aCategorie['rechten_read'])){
					echo '<tr><td class="forumtitel">';
					echo '<a href="/forum/categorie/'.$aCategorie['id'].'">'.mb_htmlentities($aCategorie['titel']).'</a><br />';
					echo mb_htmlentities($aCategorie['beschrijving']).'</td>';
					echo '<td class="forumreacties">'.$aCategorie['topics'].'</td>';
					echo '<td class="forumreacties">'.$aCategorie['reacties'].'</td>';
					echo '<td class="forumreactiemoment">';
					if($aCategorie['lastpost']=='0000-00-00 00:00:00'){
						echo 'nog geen berichten'; 
					}else{ 
						//als de dag vandaag is, niet de datum weergeven maar 'vandaag'
						echo $this->_forum->formatDatum($aCategorie['lastpost']);
						echo '<br /><a href="/forum/onderwerp/'.$aCategorie['lasttopic'].'#post'.$aCategorie['lastpostID'].'">bericht</a> door ';
						if(trim($aCategorie['lastuser'])!=''){
							echo $this->_forum->getForumNaam($aCategorie['lastuser']);
						}else{ echo 'onbekend';	}
					//er zijn nog geen berichten in deze categorie dus er is ook nog geen laatste bericht
					}
					echo '</td></tr>';
				}
			}//einde foreach
		//het forum is nog leeg, of de database is stuk ofzo
		}else{ echo '<tr><td colspan="4">Er zijn nog geen categorie&euml;n of er is iets mis met het databeest</td></tr>'; }
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
			//wellicht wel een andere pagina?
			if(isset($_GET['pagina'])){ $iPaginaID=(int)$_GET['pagina']; }else{ $iPaginaID=0; }
			$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			//als de pagina niet bestaat moet er teruggegaan worden naar de laatste pagina.
			if($iPaginaID!=0 AND $aTopics===false){
				//de pagina die opgevraagd wordt bestaat niet, gewoon maar de eerste weergeven dan.
				$iPaginaID=0;
				$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			}
			//weergeven van de navigatielinks, deze rossen we in een variabele omdat hij onderaan nogeens terug komt
			$sNavigatieLinks='<h2><a class="forumGrootlink" href="/forum/">Forum</a> &raquo; '.mb_htmlentities($sCategorie).'</h2>';
			//echo $sNavigatieLinks;
			
			//eventuele foutmelding weergeven:
			echo $this->getError();
			echo '<table class="forumtabel"><tr>';
			echo '<td class="forumhoofd">Titel</td><td class="forumhoofd">Reacties</td>';
			echo '<td class="forumhoofd">Auteur</td><td class="forumhoofd">verandering</td></tr>';
			if(is_array($aTopics)){
				//aantal topics tellen:
				$iAantalTopics=$this->_forum->topicCount($iCat);
				foreach($aTopics as $aTopic){
					//de boel klaarmaken voor weergave:
					$sOnderwerp='';
					if($aTopic['soort']=='T_POLL'){	$sOnderwerp.='[peiling] '; }
					if($aTopic['zichtbaar']=='wacht_goedkeuring'){ $sOnderwerp.='[ter goedkeuring...] '; }
					$sOnderwerp.='<a href="/forum/onderwerp/'.$aTopic['id']. '" >';
					if($aTopic['plakkerig']==1){
						$sOnderwerp.='<img src="'.CSR_PICS.'forum/plakkerig.gif" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;';
					}
					if($aTopic['open']==0){
						$sOnderwerp.='<img src="'.CSR_PICS.'forum/slotje.png" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;';
					}
					$sOnderwerp.=mb_htmlentities($aTopic['titel']).'</a>';
					$sReacties=$aTopic['reacties']-1;
					$sDraadstarter=mb_htmlentities($this->_forum->getForumNaam($aTopic['uid']));
					$sReactieMoment=$this->_forum->formatDatum($aTopic['lastpost']);
					if(trim($aTopic['lastuser'])!=''){
						$sLaatsteposter=$this->_forum->getForumNaam($aTopic['lastuser']);
					}else{ $sLaatsteposter='onbekend'; }
					#####################################
					## de boel weergeven
					#####################################
					echo "\r\n".'<tr>';
					echo '<td class="forumtitel">'.$sOnderwerp.'</td>';
					echo '<td class="forumreacties">'.$sReacties.'</td>';
					echo '<td class="forumreacties">'.$this->_forum->getForumNaam($aTopic['uid']).'</td>';
					echo '<td class="forumreactiemoment">'.$sReactieMoment;
					echo '<br /><a href="/forum/onderwerp/'.$aTopic['id'].'#post'.$aTopic['lastpostID'].'">bericht</a> door ';
					echo $sLaatsteposter;
					echo '</td></tr>'."\r\n";
				}
			}else{//$aTopics is geen array, dus bevat geen berichten.
				$iAantalTopics=0;
				echo '<tr><td colspan="3">Deze categorie bevat nog geen berichten of deze pagina bestaat niet.</td></tr>';
				$aTopic['rechten_post']=$this->_forum->getRechten_post($iCat);
			}
			//nieuw topic formuliertje
			//kijken of er wel gepost mag worden 
			echo '<tr><td colspan="3" class="forumhoofd">';
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
				//bij meer dan tien pagina's boven de tien pagina's geen links meer weergeven
				if($iAantalPaginas>10){ $iAantalPaginas=10; $bMeer=true; }
				echo 'pagina: ';
				for($iPagina=0; $iPagina<$iAantalPaginas; $iPagina++){ 
					if($iPagina==$iPaginaID){
						echo ($iPagina+1).' ';
					}else{
						echo '<a href="/forum/categorie/'.$iCat.'/'.$iPagina.'">'.($iPagina+1).'</a> ';
					}
				}
				if(isset($bMeer)){ echo '...'; }
			}
			echo '</td></tr>';
			if($this->_forum->_lid->hasPermission($aTopic['rechten_post'])){
				echo '<tr><td colspan="4" class="forumtekst"><form method="post" action="/forum/onderwerp-toevoegen/'.$iCat.'"><p>';
				if($this->_forum->_lid->hasPermission('P_LOGGED_IN')){
					echo 'Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het onderwerp waarover
						 u post hier wel thuishoort.<br /><br />';
				}else{
					//melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat wil zeggen, de topics zijn
					//nog niet direct zichtbaar.
					echo 'Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
					 &eacute;&eacute;rst door de PubCie worden goedgekeurd. <br /><span style="text-decoration: underline;">
					 Het is hierbij verplicht om uw naam en een email-adres onder het bericht te plaatsen. Dan kan de PubCie 
					 eventueel contact met u opnemen. Doet u dat niet, dan wordt u bericht waarschijnlijk niet geplaatst!<br />
					 <strong>Ook dubbelplaatsen is niet nodig, heb gewoon even geduld!</strong></span>
					 <br /><br />';
				}
				echo '
						<a class="forumpostlink" name="laatste"><strong>Titel</strong></a><br />
						<input type="text" name="titel" value="" class="tekst" style="width: 100%" tabindex="1" /><br />
						<strong>Bericht</strong>&nbsp;&nbsp; ';
				// link om het tekst-vak groter te maken.
				echo '<a href="#" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">
					invoerveld vergroten</a><br />';
				echo '<textarea name="bericht" id="forumBericht" rows="10" cols="80" style="width: 100%" class="tekst" tabindex="2"></textarea><br />
						<input type="submit" name="submit" value="verzenden" />
						</p></form></td></tr>';
			}
			echo '</table>';
			//nog eens de navigatielinks die ook bovenaan staan.
			echo $sNavigatieLinks;
		}else{
			echo '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>Dit gedeelte van het forum is niet zichtbaar voor u, of het bestaat &uuml;berhaupt niet.
				<a href="/forum/">Terug naar het forum</a>';
		}
	}
/***********************************************************************************************************
* Het Topic uiteindelijk weergeven.
*
***********************************************************************************************************/	
	function viewTopic($iTopic, $iCiteerPost=0){
		//typecasting van de variabelen.
		$iTopic=(int)$iTopic; $iCiteerPost=(int)$iCiteerPost;
		$aBerichten=$this->_forum->getPosts($iTopic);
		$rechten_post=$aBerichten[0]['rechten_post'];
		if(is_array($aBerichten) AND $this->_forum->_lid->hasPermission($aBerichten[0]['rechten_read'])){
			//navigatielinks voor in het forum weergeven:
			$sNavigatieLinks='<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
				<a href="/forum/categorie/'.$aBerichten[0]['categorie'].'" class="forumGrootlink">
					'.mb_htmlentities($this->_forum->getCategorieTitel($aBerichten[0]['categorie'])).'
				</a> &raquo; 
				'.mb_htmlentities($aBerichten[0]['titel']).'</h2>';
			//echo $sNavigatieLinks;
			echo '<h2>'.mb_htmlentities($aBerichten[0]['titel']).'</h2>';
			//eventuele foutmelding weergeven:
			echo $this->getError();
			//topic mod dingen:
			if($this->_forum->_lid->hasPermission('P_FORUM_MOD')){
				echo "\r\n".'U mag dit onderwerp modereren:<br /> ';
				//topic verwijderen
				echo '<a href="/forum/verwijder-onderwerp/'.$iTopic.'" onclick="return confirm(\'Weet u zeker dat u dit topic wilt verwijderen?\')" class="knop">verwijderen</a> ';
				if($aBerichten[0]['open']==1){
					echo ' <a href="/forum/sluit-onderwerp/'.$iTopic.'" class="knop">sluiten (reageren niet meer mogelijk)</a> ';
				}else{
					echo ' <a href="/forum/open-onderwerp/'.$iTopic.'" class="knop">weer openen (reageren weer w&eacute;l mogelijk)</a> ';
				}	
				if($aBerichten[0]['plakkerig']==0){
					echo ' <a href="/forum/maak-plakkerig/'.$iTopic.'" class="knop">maak plakkerig</a> ';
				}else{
					echo ' <a href="/forum/maak-niet-plakkerig/'.$iTopic.'" class="knop">verwijder plakkerigheid</a> ';
				}
				if($aBerichten[0]['zichtbaar']=='wacht_goedkeuring'){
					echo ' <a href="/forum/keur-goed/'.$aBerichten[0]['postID'].'">Keur dit bericht goed.</a> ';
				}
				echo '<br /><br />'."\r\n";
			}
			echo '<table class="forumtabel">
			<tr><td class="forumtussenschot" colspan="2"></td></tr>';
			//speciale topic weergeven als het topic er een is. bijvoorbeeld een poll;
			switch($aBerichten[0]['soort']){
				case 'T_POLL':
					require_once('class.forumpoll.php');
					$poll=new ForumPoll($this->_forum);
					//dingen ophalen
					$aPollOpties=$poll->getPollOpties($iTopic);
					$iPollStemmen=$poll->getPollStemmen($iTopic);
					$iPollOpties=count($aPollOpties);
					//er mag maar één keer per *ingellogged lid* per poll gestemd worden, en alleen als het topic open is.
					$bMagStemmen=$poll->uidMagStemmen($iTopic, $rechten_post) AND ($aBerichten[0]['open']==1);
					$sPolleigenaar=$poll->peilingVan($aBerichten[0]['startUID']);
					//html dan maer
					echo '<tr><td class="forumauteur">Een peiling van '.mb_htmlentities($sPolleigenaar).':<br />';
					echo '<br /><br />Er is '.$iPollStemmen.' keer gestemd.</td><td class="forumbericht0">';
					echo '<form action="/forum/stem/'.$iTopic.'" method="post">';
					echo '<table id="pollTabel">';
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
						//formulier enkel tonen als er gestemd mag worden
						if($bMagStemmen){
							echo '<input type="radio" name="pollOptie" id="'.$aPollOptie['id'].'" value="'.$aPollOptie['id'].'" />';
						}
						echo '<label for="'.$aPollOptie['id'].'">'.mb_htmlentities($aPollOptie['optie']).'</label></td>';
						echo '<td><img src="'.CSR_PICS.'forum/frikandel.png" height="20px" width="'.$iBalkLengte.'px" title="een del, lekker!" /></td>';
						echo '<td style="width: 80px">'.round( $fPercentage, 2).'% ('.$aPollOptie['stemmen'].')</td></tr>';
					}
					//verzendknopje enkel tonen als er gestemd mag worden
					if($bMagStemmen){
						echo '<tr><td colspan="3"><input type="submit" value="stemmen" name="stemmen" /> <em>(Als u hier klikt wordt uw eventuele commentaar niet opgeslagen)</em></td></tr>';
					}
					echo '</table></form></td></tr>';
					//tussenlijntje
					echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				break;
				//hier kunnen nog dingen mee gedaan worden, bijvoorbeeld een andere layout/kleur voor een lezing
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
				echo $this->_forum->getForumNaam($aBericht['uid'], $aBericht).' schreef ';
				//anker maken met post-ID
				echo '<a id="post'.$aBericht['postID'].'"></a>';
				echo $this->_forum->formatDatum($aBericht['datum']);
				if($aBericht['bewerkDatum']!='0000-00-00 00:00:00'){
					echo ';<br />Bewerkt '.$this->_forum->formatDatum($aBericht['bewerkDatum']);
				}
				echo '<br />';
				//citeer knop enkel als het topic open is en als men mag posten, of als men mod is.
				if(($aBericht['open']==1 AND $this->_forum->_lid->hasPermission($rechten_post)) OR 
					$this->_forum->_lid->hasPermission('P_FORUM_MOD')){
					echo ' <a href="/forum/reactie/'.$aBericht['postID'].'#laatste"><img src="'.CSR_PICS.'forum/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" /></a> ';
				}
				//bewerken als bericht van gebruiker is, of als men mod is.
				if($this->_forum->magBewerken($aBericht['postID'], $aBericht['uid'], $aBericht['open'], $rechten_post)){
					echo '<a href="/forum/bewerken/'.$aBericht['postID'].'">
						<img src="'.CSR_PICS.'forum/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" /></a> ';
				}
				//verwijderlinkje, niet als er maar een bericht in het onderwerp is.
				if($iBerichtenAantal!=1 AND $this->_forum->_lid->hasPermission('P_FORUM_MOD')){
					echo '<a href="/forum/verwijder-bericht/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u deze post wilt verwijderen?\')">';
					echo '<img src="'.CSR_PICS.'forum/verwijderen.png" title="Verwijder bericht" alt="Verwijder bericht" style="border: 0px;" /></a>';
				}
				//goedkeuren van berichten
				if($this->_forum->_lid->hasPermission('P_FORUM_MOD') AND $aBericht['zichtbaar']=='wacht_goedkeuring'){
					echo '<br /><a href="/forum/keur-goed/'.$aBericht['postID'].'" onclick="return confirm(\'Weet u zeker dat u dit bericht wilt goedkeuren?\')">bericht goedkeuren</a>';
					echo '<br /><a href="/tools/stats.php?ip='.$aBericht['ip'].'">ip-log</a>';
				}
				echo '</td>';
				
				//het eigenlijke bericht weergeven.
				echo "\r\n".'<td class="forumbericht'.($iWissel%2).'">';
				$sBericht=$aBericht['tekst'];
				//als er woorden hooggelicht moeten worden
				if(isset($_GET['highlight']) AND preg_match('/[a-zA-Z0-9\+\-]/', $_GET['highlight'])){
					$sZoekWoorden=urldecode($_GET['highlight']);
					$sZoekWoorden=str_replace(array('+', '"', "'", '/','\\'), '', $sZoekWoorden);
					$aZoekWoorden=explode(' ', $sZoekWoorden);
					foreach($aZoekWoorden as $sZoekWoord){
						//als het een leeg zoekwoord betreft of een zoekwoord dat juist uitgesloten zou moeten worden dan niet highlighten
						if($sZoekWoord!='' AND $sZoekWoord[0]!='-'){
							//ubb tag invoegen voor het highlighten van de zoekwoorden
							$sBericht=preg_replace('/('.$sZoekWoord.')/i', '[zoekwoord:'.$aBericht['bbcode_uid'].']\\1[/zoekwoord:'.$aBericht['bbcode_uid'].']', $sBericht);
						}
					}
				}
				$sBericht=bbview($sBericht, $aBericht['bbcode_uid']);
				//æ's maken van alle aa's
				//$sBericht=str_replace('aa', '&aelig;', $sBericht);
				//$sBericht=str_replace('AA', '&AElig;', $sBericht);
#				$sBericht=str_replace('666', '<a href="http://www.biblija.net/biblija.cgi?m=Op+8%3A13&compact=1&id18=1&pos=1&set=10&lang=nl">www</a>', $sBericht);
				echo $sBericht.'</td></tr>';
				//tussenlijntje
				echo '<tr><td class="forumtussenschot" colspan="2"></td></tr>'."\r\n";
				$iWissel++;
			}//einde foreach $aBerichten
			//nu nog ff een quickpost formuliertje
			echo '<tr><td class="forumauteur">';
			if($iCiteerPost==0){
				echo '<a class="forumpostlink" id="laatste">Snel reageren:</a><br /><br />';
				$iTekstareaRegels=6;
			}else{
				echo '<a class="forumpostlink" id="laatste"><stong>Citeren:</strong></a><br /><br />';
				$iTekstareaRegels=20;
			}
			if($this->_forum->magBerichtToevoegen($iTopic, $aBericht['open'], $aBericht['rechten_post'])){	
				// link om het tekst-vak groter te maken.
				echo '<a href="#laatste" onclick="vergrootTextarea(\'forumBericht\', 10)" title="Vergroot het invoerveld">
					invoerveld vergroten&nbsp;&raquo;</a><br />';
			}			
			//berichtje weergeven  voor moderators als het topic gesloten is.
			if($this->_forum->_lid->hasPermission('P_FORUM_MOD') AND $aBericht['open']==0){
				echo '<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>';
			}
			echo '</td><td class="forumtekst">';
			if($this->_forum->magBerichtToevoegen($iTopic, $aBericht['open'], $rechten_post)){ 
				echo '<form method="post" action="/forum/toevoegen/'.$iTopic.'"><p>';
				//berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden.
				if(!$this->_forum->_lid->hasPermission('P_LOGGED_IN')){
					echo '<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/groepen/commissie/PubCie.html">PubCie</a>. Het vermelden van <em>uw naam</em> verhoogt de kans dat dit gebeurt.</strong><br /><br />';
				}
				echo '<textarea name="bericht" id="forumBericht" class="tekst" rows="'.$iTekstareaRegels.'" cols="80" style="width: 100%;" >';
				//inhoud van de textarea vullen met eventuele quote...
				if($iCiteerPost!=0){
					$aPost=$this->_forum->getPost((int)$iCiteerPost);
					$sCiteerBericht=bbedit($aPost['tekst'], $aPost['bbcode_uid']);
					echo '[citaat]'.$sCiteerBericht.'[/citaat]';
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
			if($iBerichtenAantal>4){ echo $sNavigatieLinks; }
		}else{
			if(!is_array($aBerichten)){
				echo 'Onderwerp bestaat helaas niet (meer).';
			}else{
				echo '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>
				Dit gedeelte van het forum is niet beschikbaar voor u, u zult moeten inloggen, of terug gaan 
					naar <a href="/forum/">het forum</a>';
			}
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
				echo  '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; 
					<a href="/forum/categorie/'.$aPost['categorieID'].'" class="forumGrootlink">
						'.mb_htmlentities($aPost['categorieTitel']).'
					</a> &raquo; <a href="/forum/onderwerp/'.$iTopicID.'#post'.$iPostID.'" class="forumGrootlink">
					'.mb_htmlentities($aPost['topicTitel']).'</a> &raquo; bericht bewerken</h2>';
				
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
					<input type="submit" name="submit" value="verzenden" /> <a href="/forum/onderwerp/'.$iTopicID.'">terug naar onderwerp</a>
					</form></td></tr></table>';
			}else{
				echo '<h2>Dit bericht bestaat niet.</h2>Terug naar <a href="/forum/">het forum.</a>';
			}
		}else{
			$iTopicID=$this->_forum->getTopicVoorPostID($iPostID);
			echo '<h2><a href="/forum/" class="forumGrootlink">Forum</a> &raquo; Dit bericht mag u niet bewerken.</h2>
				Terug naar <a href="/forum/onderwerp/'.$iTopicID.'">Vergeet bewerken, ga terug naar het onderwerp waar u vandaan kwam.</a>';
		}
	}
/***********************************************************************************************************
* poll toevoegen
*
***********************************************************************************************************/	
	function pollFormulier($iCatID){
		$iCatID=(int)$iCatID;
		$sTitel=$sBericht='';
		//bij foutmeldingen de berichten uit de post variabelen halen
		if(isset($_POST['titel'])){ $sTitel=trim($_POST['titel']); }
		if(isset($_POST['bericht'])){ $sBericht=trim($_POST['bericht']); }
		echo '<form action="/forum/maak-stemming/'.$iCatID.'" method="post"><table class="forumtabel">
					<tr><td colspan="3" class="forumhoofd">Peiling toevoegen</td><td class="forumhoofd"></td></tr>
					<tr><td colspan="4" class="forumtekst">';
		//eventuele foutmelding weergeven.
		echo $this->getError();
		echo '<strong>Vraag/stelling</strong><br />';
		echo '<input type="text" name="titel" value="'.$sTitel.'" style="width: 100%" class="tekst" /><br />';
		echo '</td></tr><tr><td colspan="4" class="forumtekst">';
		echo '<strong>Opties voor de peiling:</strong><br />Lege velden worden genegeerd.<br /><br />';
		for($iTeller=0; $iTeller<15; $iTeller++){
			if(isset($_POST['opties'][$iTeller]) AND trim($_POST['opties'][$iTeller])!=''){
				$sOptie=trim($_POST['opties'][$iTeller]);
			}else{
				$sOptie='';
			}
			echo ($iTeller+1).'. <input type="text" name="opties[]" value="'.$sOptie.'" style="width: 70%" class="tekst" /><br />';
		}
		echo '</td></tr><tr><td colspan="4" class="forumtekst"><strong>Bericht</strong><br />';
		echo '<textarea name="bericht" rows="10" style="width: 100%" class="tekst">'.$sBericht.'</textarea><br />';
		echo '<input type="submit" name="submit" value="verzenden" /> <a href="/forum/categorie/'.$iCatID.'">terug naar categorie</a>';
		echo '</td></tr></table></form>';
	}
/***********************************************************************************************************
* rss feed weergeven van het forum.
*
***********************************************************************************************************/
	function rssFeed(){
		$aPosts=$this->_forum->getPostsVoorRss(false, false);
		$datum=date('r');
		//hoofder maeken
		?>
<rss version="2.0">
	<channel>
		<copyright>Copyright 2006 C.S.R. Delft</copyright>
		<pubDate><?php echo $datum; ?></pubDate>
		<lastBuildDate><?php echo $datum; ?></lastBuildDate>
		<docs>http://csrdelft.nl/intern/index.php</docs>
		<description>
			C.S.R. Delft: Vereniging van Christen-studenten te Delft.
		</description>
		<image>
			<link>http://csrdelft.nl/</link>
			<title>C.S.R. Delft</title>
			<url><?php echo CSR_PICS; ?>layout/beeldmerk.jpg</url>
			<height>150</height>
			<width>118</width>
			<description>Logo van C.S.R. Delft</description>
		</image>
		<language>nl-nl</language>
		<link>http://csrdelft.nl/forum/</link>
		<title>C.S.R. Delft forum laatste berichten.</title>
		<managingEditor>PubCie@csrdelft.nl</managingEditor>
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
			$tekst=preg_replace('/(\[(|\/)\w+:(1:)?'.$bbcode_uid.'\])/', '|', $tekst);
			//$volledigetekst=$tekst=preg_replace('/(\[(|\/)url=http://[a-f0-9]+:[a-f0-9]+\])/', '|', $volledigetekst);
			$volledigetekst=$tekst;
			if(kapStringNetjesAf($tekst, 50)){
				$tekst.='...';
			}
			echo '<item>';
			echo '<title>'.$this->_forum->_lid->getNaamLink($aPost['uid'], 'nick', false, $aPost, false).': '.str_replace(array("\r\n", "\r", "\n"), ' ', $tekst).'</title>';
			echo '<link>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'#post'.$aPost['postID'].'</link>';
			
			echo '<description>'.$volledigetekst.'</description>';
			echo '<author>'.$this->_forum->getForumNaam($aPost['uid'], $aPost, false, false).'</author>';
			echo '<category>forum: '.htmlspecialchars($aPost['titel']).'</category>';
			echo '<comments>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'</comments>';
			echo '<guid>http://csrdelft.nl/forum/onderwerp/'.$aPost['tid'].'#post'.$aPost['postID'].'</guid>';
			echo '<pubDate>'.$pubDate.'</pubDate>';
			echo '</item>';
		}
		echo '</channel>';
		echo '</rss>';
	}
/***********************************************************************************************************
* Kort rijtje met laatste posts.
*
***********************************************************************************************************/
	function lastPosts(){
		$aPosts=$this->_forum->getPostsVoorRss(15, true);
		echo '<div id="forumHighlights"><a href="/forum/" class="kopje">Laatste forumberichten:</a><br />';
		foreach($aPosts as $aPost){
			//$tekst=$aPost['nickname'].': ';
			$tekst=$aPost['titel'];
			if(strlen($tekst)>19){
				$tekst=trim(substr($tekst, 0, 16)).'..';
			}
			$post=preg_replace('/(\[(|\/)\w+:'.$aPost['bbcode_uid'].'\])/', '|', $aPost['tekst']);
			$postfragment=substr(str_replace(array("\n", "\r", ' '), ' ', $post), 0, 40);
			echo '<span class="tijd">'.date('H:i', strtotime($aPost['datum'])).'</span> ';
			echo '<a href="/forum/onderwerp/'.$aPost['tid'].'#post'.$aPost['postID'].'" 
				title="['.htmlspecialchars($aPost['titel']).'] '.
					$this->_forum->getForumNaam($aPost['uid'], $aPost, false).': '.mb_htmlentities($postfragment).'">
				'.$tekst.'
				</a><br />'."\n";
		}
		echo '</div>';
	}
/***********************************************************************************************************
* Zoekah in forumposts, en titels van onderwerpen
*
***********************************************************************************************************/
	function zoeken(){
		$sZoekQuery='';
		if(isset($_POST['zoeken'])){ $sZoekQuery=trim($_POST['zoeken']); }elseif(isset($_GET['zoeken'])){ $sZoekQuery=trim($_GET['zoeken']);}
		
		echo '<h1>Zoeken in het forum</h1>Hier kunt u zoeken in het forum. Zoeken kan met boleaanse zoekparameters, uitleg is 
			<a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html">te vinden op de pagina daarover in de mysql handleiding</a>.';
		//altijd het zoekformulier weergeven.
		$this->zoekFormulier($sZoekQuery);
		if($sZoekQuery!=''){
			$aZoekResultaten=$this->_forum->searchPosts($sZoekQuery);
			if(is_array($aZoekResultaten)){
				$aZoekOnderdelen=explode(' ', $sZoekQuery);
				$sEersteTerm=$aZoekOnderdelen[0];
				echo 'In <em>'.count($aZoekResultaten).'</em> onderwerpen kwam de volgende zoekterm voor: <strong>'.mb_htmlentities($sZoekQuery).'</strong>';
				echo '<br /><br /><table class="forumtabel"><tr><td class="forumhoofd">onderwerp</td><td class="forumhoofd">auteur</td>';
				echo '<td class="forumhoofd">categorie</td><td class="forumhoofd">datum</td></tr>';
				foreach($aZoekResultaten as $aZoekResultaat){
					$iFragmentLengte=250;
					//ubb wegslopen
					$sPostFragment=preg_replace('/\[\/?[a-z\*\:]*:'.$aZoekResultaat['bbcode_uid'].'\]/', '', $aZoekResultaat['tekst']);
					$sPostFragment=preg_replace('/\[url=.*\](.*)\[\/url\]/', '\\1', $sPostFragment);
					$sPostFragment=preg_replace('/\[\/?[a-z\*\:]*:'.$aZoekResultaat['bbcode_uid'].'\?/', '', $sPostFragment);
					
					//is het bericht zelf al korter dan de fragmentlengte?
					if(strlen($sPostFragment)>=$iFragmentLengte){
						//beginpositie en lengte van het te tonen fragment proberen te berekenen.
						$iEersteTermPos=strpos($aZoekResultaat['tekst'], $sEersteTerm);
						if($iEersteTermPos<(.5*$iFragmentLengte)){ $iBegin=0; }else{ $iBegin=$iEersteTermPos-(.5*$iFragmentLengte); }
						if($iBegin+$iFragmentLengte>=strlen($aZoekResultaat['tekst'])){ 
							$iLengte=strlen($aZoekResultaat['tekst'])-$iEersteTermPos; 
						}else{ 
							$iLengte=$iFragmentLengte;
						}
						//het fragment eruit halen
						$sPostFragment=substr($sPostFragment, $iBegin, $iLengte);
						if($iBegin!=0){ $sPostFragment='...'.trim($sPostFragment); };
					}
					$sPostFragment=mb_htmlentities($sPostFragment);
					//zoektermen hooglichten
					$sPostFragment=preg_replace('/('.$sEersteTerm.')/i', '<strong>\\1</strong>', $sPostFragment);

					echo '<tr><td class="forumtitel">';
					echo '<a href="/forum/onderwerp/'.$aZoekResultaat['tid'].'/'.urlencode($sZoekQuery).'#post'.$aZoekResultaat['postID'].'">';
					echo $aZoekResultaat['titel'].'</a>';
					if($aZoekResultaat['aantal']!=1){ echo ' <em>('.$aZoekResultaat['aantal'].' berichten in dit onderwerp)</em>'; }
					echo '<br />'.$sPostFragment.'</td>';
					echo '<td class="forumtitel">'.$this->_forum->getForumNaam($aZoekResultaat['uid'],$aZoekResultaat).'</td>';
					echo '<td class="forumtitel">
						<a href="/forum/categorie/'.$aZoekResultaat['categorie'].'">'.$aZoekResultaat['categorieTitel'].'</a></td>';
					echo '<td class="forumtitel">
						'.$aZoekResultaat['datum'].'</td>';
					echo '</tr>';
				}
			echo '</table>';
			}else{ echo '<h3>Er is niets gevonden</h3>Probeer het opnieuw. (Zoekresultaten moeten minimaal 4 letters bevatten)'; }
		}
	}
	function zoekFormulier($sZoekQuery=''){
		$sZoekQuery=htmlspecialchars($sZoekQuery, ENT_QUOTES, 'UTF-8');
		echo '<form action="/forum/zoeken.php" method="post"><p><input type="text" value="'.$sZoekQuery.'" name="zoeken" />';
		echo '<input type="submit" value="zoeken" name="verzenden" /></p></form><br />';
	}
	
	function getError(){
		if(isset($_SESSION['forum_foutmelding'])){
			$sError='<div class="foutmelding">'.mb_htmlentities(trim($_SESSION['forum_foutmelding'])).'</div>';
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
	function viewWaarbenik(){
		if(	($this->_actie=='topic' AND isset($_GET['topic'])) OR 
				($this->_actie=='citeren' AND isset($_GET['post'])) ){
			if(isset($_GET['topic'])){
				$iTopicID=(int)$_GET['topic'];
			}else{
				$iTopicID=$this->_forum->getTopicVoorPostID((int)$_GET['post']);
			}		
			$iCategorieID=$this->_forum->getCategorieVoorTopic($iTopicID);	
			$sCategorie=$this->_forum->getCategorieTitel($this->_forum->getCategorieVoorTopic($iTopicID));
			$sTitel='<a href="/forum/">Forum</a> &raquo; <a href="/forum/categorie/'.$iCategorieID.'">'.$sCategorie.'</a> &raquo; '.$this->_forum->getTopicTitel($iTopicID);
		}elseif($this->_actie=='forum' AND isset($_GET['forum'])){
			$sTitel='<a href="/forum/">Forum</a> &raquo; '.$this->_forum->getCategorieTitel((int)$_GET['forum']);
		}elseif($this->_actie=='zoeken'){
			$sTitel='<a href="/forum/">Forum</a> &raquo; zoeken';
		}else{
			$sTitel='Forum';
		}
		echo $sTitel;
	}
	function getTitel(){
		$sTitel='Forum - ';
		if(	($this->_actie=='topic' AND isset($_GET['topic'])) OR 
				($this->_actie=='citeren' AND isset($_GET['post'])) ){
			if(isset($_GET['topic'])){
				$iTopicID=(int)$_GET['topic'];
			}else{
				$iTopicID=$this->_forum->getTopicVoorPostID((int)$_GET['post']);
			}
			$sCategorie=$this->_forum->getCategorieTitel($this->_forum->getCategorieVoorTopic($iTopicID));
			$sTitel.=$sCategorie.' - '.$this->_forum->getTopicTitel($iTopicID);
		}elseif($this->_actie=='forum' AND isset($_GET['forum'])){
			$sTitel.=$this->_forum->getCategorieTitel((int)$_GET['forum']);
		}elseif($this->_actie=='zoeken'){
			$sTitel.='zoeken';
		}else{
			$sTitel='Forum';
		}
		return $sTitel; 
	}
	function view(){
		switch($this->_actie){
			case 'forum': if(isset($_GET['forum'])){ $this->viewTopics((int)$_GET['forum']); }else{ $this->viewCategories(); } break;
			case 'topic': $this->viewTopic((int)$_GET['topic']); break;
			case 'nieuw-poll':
				if(isset($_GET['cat']) AND $this->_forum->catExistsVoorUser($_GET['cat'])){
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
			case 'lastposts': $this->lastPosts(); break;
			case 'zoeken': $this->zoeken(); break;
			default: $this->viewCategories();	break;
		}
		if($this->_forum->_lid->hasPermission('P_FORUM_MOD') AND $this->_actie!='rss' AND isset($_SESSION['debug'])){
			echo '<br />forum parsetijd: '.round($this->_forum->getParseTime(), 4).' seconden';
			echo '<pre>'.print_r($_GET, true).'</pre>';
		}
	}
}
?>
