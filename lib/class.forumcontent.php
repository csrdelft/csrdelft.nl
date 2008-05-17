<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumcontent.php
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class ForumContent extends SimpleHTML {
	var $_forum;
	var $_actie;
	var $_sTitel='forum';
	
	var $_topicsPerPagina;
	
	function ForumContent($bForum, $actie){
		$this->_forum=$bForum;
		$this->_actie=$actie;
		$this->_topicsPerPagina=$bForum->getTopicsPerPagina();
	}
/***********************************************************************************************************
* Overzicht van Categorieën met aantal topics en posts
*
***********************************************************************************************************/	
	function viewCategories(){
		$aCategories=$this->_forum->getCategories(true);
		echo '<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value=\'\';" /></form>';
		echo '<h1>Forum</h1>';
		//eventuele foutmelding weergeven:
		echo $this->getMelding();
		echo '<table class="forumtabel">
			<tr>
				<td class="forumhoofd">Forum</td>
				<td class="forumhoofd">onderwerpen</td>
				<td class="forumhoofd">berichten</td>
				<td class="forumhoofd">verandering</td>
			</tr>';
		if(is_array($aCategories)){
			foreach($aCategories as $aCategorie){
				if($aCategorie['titel']=='SEPARATOR'){
					echo '<tr><td class="forumtussenschot" colspan="4"></td></tr>';
				}else{
					echo '<tr><td class="forumtitel">';
					echo '<a href="/communicatie/forum/categorie/'.$aCategorie['id'].'">'.mb_htmlentities($aCategorie['titel']).'</a><br />';
					echo mb_htmlentities($aCategorie['beschrijving']).'</td>';
					echo '<td class="forumreacties">'.$aCategorie['topics'].'</td>';
					echo '<td class="forumreacties">'.$aCategorie['reacties'].'</td>';
					echo '<td class="forumreactiemoment">';
					if($aCategorie['lastpost']=='0000-00-00 00:00:00'){
						echo 'nog geen berichten'; 
					}else{ 
						echo $this->_forum->formatDatum($aCategorie['lastpost']);
						echo '<br /><a href="/communicatie/forum/onderwerp/'.$aCategorie['lasttopic'].'#post'.$aCategorie['lastpostID'].'">bericht</a> door ';
						if(trim($aCategorie['lastuser'])!=''){
							echo $this->_forum->getForumNaam($aCategorie['lastuser']);
						}
					}
					echo '</td></tr>';
				}
			}//einde foreach
		}else{ 
			//het forum is nog leeg, of de database is stuk ofzo
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
		
		//topics ophaelen voor deze categorie
		//wellicht wel een andere pagina?
		if(isset($_GET['pagina'])){ 
			$iPaginaID=(int)$_GET['pagina']; 
		}else{
			$iPaginaID=0; 
		}
		if($this->_forum->catExistsVoorUser($iCat)){
			$sCategorie=$this->_forum->getCategorieTitel($iCat);
			$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			//als de pagina niet bestaat moet er teruggegaan worden naar de laatste pagina.
			if($iPaginaID!=0 AND $aTopics===false){
				//de pagina die opgevraagd wordt bestaat niet, gewoon maar de eerste weergeven dan.
				$iPaginaID=0;
				$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			}
			$iAantalTopics=$this->_forum->topicCount($iCat);
		}elseif($iCat==0){
			$sCategorie='Laatste forumberichten';
			$this->_topicsPerPagina=40;
			$aTopics=$this->_forum->getPostsVoorRss($this->_topicsPerPagina);
			$iAantalTopics=count($aTopics);
		}else{
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>Dit gedeelte van het forum is niet zichtbaar voor u, of het bestaat &uuml;berhaupt niet.
				<a href="/communicatie/forum/">Terug naar het forum</a>';
			return;
		}
		
		echo '<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value=\'\';" /></form>';
		echo '<div class="forumNavigatie"><a href="/communicatie/forum/" class="forumGrootlink">Forum</a>';
		echo '<h1>'.mb_htmlentities(wordwrap($this->_forum->getCategorieTitel($iCat), 80, "\n", true)).'</h1></div>';
		
		//eventuele foutmelding weergeven:
		echo $this->getMelding();
		echo '<table class="forumtabel"><tr>';
		echo '<td class="forumhoofd">Titel</td><td class="forumhoofd">Reacties</td>';
		echo '<td class="forumhoofd">Auteur</td><td class="forumhoofd">verandering</td></tr>';
		if(is_array($aTopics)){
			foreach($aTopics as $aTopic){
				//klein hackje om de array van getPostRss compatible te maken met die van getTopic
				if($iCat==0){
					$aTopic['id']=$aTopic['tid'];
					$aTopic['lastpostID']=$aTopic['postID'];
					$aTopic['lastuser']=$aTopic['uid'];
				}
				//de boel klaarmaken voor weergave:
				$sOnderwerp='';
				if($aTopic['soort']=='T_POLL'){	$sOnderwerp.='[peiling] '; }
				if($aTopic['zichtbaar']=='wacht_goedkeuring'){ $sOnderwerp.='[ter goedkeuring...] '; }
				$sOnderwerp.='<a href="/communicatie/forum/onderwerp/'.$aTopic['id']. '" >';
				if($aTopic['plakkerig']==1){
					$sOnderwerp.='<img src="'.CSR_PICS.'forum/plakkerig.gif" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;';
				}
				if($aTopic['open']==0){
					$sOnderwerp.='<img src="'.CSR_PICS.'forum/slotje.png" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;';
				}
				$sOnderwerp.=mb_htmlentities(wordwrap($aTopic['titel'], 60, "\n", true)).'</a>';
				$sReacties=$aTopic['reacties']-1;
				$sDraadstarter=mb_htmlentities($this->_forum->getForumNaam($aTopic['uid']));
				$sReactieMoment=$this->_forum->formatDatum($aTopic['lastpost']);
				if(trim($aTopic['lastuser'])!=''){
					$sLaatsteposter=$this->_forum->getForumNaam($aTopic['lastuser']);
				}else{
					$sLaatsteposter='onbekend'; 
				
				}

				echo "\r\n".'<tr>';
				echo '<td class="forumtitel">'.$sOnderwerp.'</td>';
				echo '<td class="forumreacties">'.$sReacties.'</td>';
				echo '<td class="forumreacties">'.$this->_forum->getForumNaam($aTopic['uid']).'</td>';
				echo '<td class="forumreactiemoment">'.$sReactieMoment;
				echo '<br /><a href="/communicatie/forum/onderwerp/'.$aTopic['id'].'#post'.$aTopic['lastpostID'].'">bericht</a> door ';
				echo $sLaatsteposter;
				echo '</td></tr>'."\r\n";
			}
		}else{//$aTopics is geen array, dus bevat geen berichten.
			$iAantalTopics=0;
			echo '<tr><td colspan="3">Deze categorie bevat nog geen berichten of deze pagina bestaat niet.</td></tr>';
		}
		
		
		echo '<tr><td colspan="3" class="forumhoofd">&nbsp;</td>';
		
		/*
		 * Pagineringslinkjes
		 */
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
					echo '<a href="/communicatie/forum/categorie/'.$iCat.'/'.$iPagina.'">'.($iPagina+1).'</a> ';
				}
			}
			if(isset($bMeer)){ echo '...'; }
		}
		echo '</td></tr>';
		
		
		
		/*
		 * Begin van het invoeren van een nieuw bericht
		 */
		 
		$lid=Lid::get_Lid();
		if($lid->hasPermission($aTopic['rechten_post'])){
			echo '<tr><td colspan="4" class="forumtekst"><form method="post" action="/communicatie/forum/onderwerp-toevoegen/'.$iCat.'"><p>';
			if($lid->hasPermission('P_LOGGED_IN')){
				echo 'Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het onderwerp waarover
					 u post hier wel thuishoort.<br /><br />';
			}else{
				//melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat wil zeggen, de topics zijn
				//nog niet direct zichtbaar.
				echo 'Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
				 &eacute;&eacute;rst door de PubCie worden goedgekeurd. <br /><span style="text-decoration: underline;">
				 Het is hierbij verplicht om uw naam en een email-adres onder het bericht te plaatsen. Dan kan de PubCie 
				 eventueel contact met u opnemen. Doet u dat niet, dan wordt uw bericht waarschijnlijk niet geplaatst!<br />
				 <strong>Ook dubbelplaatsen is niet nodig, heb gewoon even geduld!</strong></span>
				 <br /><br />';
			}
			echo '
					<a class="forumpostlink" name="laatste"><strong>Titel</strong></a><br />
					<input type="text" name="titel" value="" class="tekst" style="width: 100%" tabindex="1" /><br />
					<strong>Bericht</strong>&nbsp;&nbsp; ';
			// link om het tekst-vak groter te maken.
			echo '<a href="#" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">
				Invoerveld vergroten</a><br />';
			echo '<textarea name="bericht" id="forumBericht" rows="10" cols="80" style="width: 100%" class="tekst" tabindex="2"></textarea><br />
					<input type="submit" name="submit" value="verzenden" />
					</p></form></td></tr>';
		}
		echo '</table>';
		//nog eens de navigatielinks die ook bovenaan staan.
		echo $sNavigatieLinks;
	
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
				$aPost=$this->_forum->getSinglePost($iPostID);
				//navigatielinks
				echo  '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; 
					<a href="/communicatie/forum/categorie/'.$aPost['categorieID'].'" class="forumGrootlink">
						'.mb_htmlentities($aPost['categorieTitel']).'
					</a> &raquo; <a href="/communicatie/forum/onderwerp/'.$iTopicID.'#post'.$iPostID.'" class="forumGrootlink">
					'.mb_htmlentities($aPost['topicTitel']).'</a> &raquo; bericht bewerken</h2>';
				
				echo '<table class="forumtabel">
					<tr><td colspan="3" class="forumhoofd">Bericht bewerken</td><td class="forumhoofd">&nbsp;</td></tr>
					<tr><td colspan="4" class="forumtekst">
					<form method="post" action="/communicatie/forum/bewerken/'.$iPostID.'">
					<h3>Als u dingen aanpast zet er dan even bij w&aacute;t u aanpast! Geef bijvoorbeeld even aan wat u heeft aangepast in een [offtopic]...[/offtopic]-tag onder uw bericht.</h3>
					<strong>Bericht</strong>&nbsp;&nbsp;';
				// link om het tekst-vak groter te maken.
				echo '<a href="#" onclick="vergrootTextarea(\'forumBericht\', 10)" name="Vergroot het invoerveld">Invoerveld vergroten</a><br />';

				echo '
					<textarea name="bericht" id="forumBericht" rows="20" style="width: 100%" class="tekst">'.
						$aPost['tekst'].'</textarea><br />
					<input type="submit" name="submit" value="verzenden" /> <a href="/communicatie/forum/onderwerp/'.$iTopicID.'">terug naar onderwerp</a>
					</form></td></tr></table>';
			}else{
				echo '<h2>Dit bericht bestaat niet.</h2>Terug naar <a href="/communicatie/forum/">het forum.</a>';
			}
		}else{
			$iTopicID=$this->_forum->getTopicVoorPostID($iPostID);
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Dit bericht mag u niet bewerken.</h2>
				Terug naar <a href="/communicatie/forum/onderwerp/'.$iTopicID.'">Vergeet bewerken, ga terug naar het onderwerp waar u vandaan kwam.</a>';
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
		echo '<form action="/communicatie/forum/maak-stemming/'.$iCatID.'" method="post"><table class="forumtabel">
					<tr><td colspan="3" class="forumhoofd">Peiling toevoegen</td><td class="forumhoofd"></td></tr>
					<tr><td colspan="4" class="forumtekst">';
		//eventuele foutmelding weergeven.
		echo $this->getMelding();
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
		echo '<input type="submit" name="submit" value="verzenden" /> <a href="/communicatie/forum/categorie/'.$iCatID.'">terug naar categorie</a>';
		echo '</td></tr></table></form>';
	}
/***********************************************************************************************************
* rss feed weergeven van het forum.
*
***********************************************************************************************************/
	function rssFeed(){
		$aPosts=$this->_forum->getPostsVoorRss(false, false);
		
		$rss=new Smarty_csr();
		$rss->assign('aPosts', $aPosts);
		
		$rss->display('forum/rss.tpl');
	}
/***********************************************************************************************************
* Kort rijtje met laatste posts.
*
***********************************************************************************************************/
	function lastPosts(){
		$aPosts=$this->_forum->getPostsVoorRss(15, true);
		echo '<h1><a href="/communicatie/forum/categorie/laatste">Forum</a></h1>';
		foreach($aPosts as $aPost){
			//$tekst=$aPost['nickname'].': ';
			$tekst=$aPost['titel'];
			if(strlen($tekst)>20){
				$tekst=str_replace(' ', '&nbsp;', trim(substr($tekst, 0, 18)).'…');
			}
			$post=preg_replace('/(\[(|\/)\w+\])/', '|', $aPost['tekst']);
			$postfragment=substr(str_replace(array("\n", "\r", ' '), ' ', $post), 0, 40);
			echo '<div class="item"><span class="tijd">'.date('H:i', strtotime($aPost['datum'])).'</span>&nbsp;';
			echo '<a href="/communicatie/forum/onderwerp/'.$aPost['tid'].'#post'.$aPost['postID'].'" 
				title="['.htmlspecialchars($aPost['titel']).'] '.
					$this->_forum->getForumNaam($aPost['uid'], $aPost, false).': '.mb_htmlentities($postfragment).'">'.$tekst.'</a><br />'."\n";
			echo '</div>';
		}
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
					$sPostFragment=preg_replace('/\[\/?[a-z\*\:]*\]/', '', $aZoekResultaat['tekst']);
					$sPostFragment=preg_replace('/\[url=.*\](.*)\[\/url\]/', '\\1', $sPostFragment);
					$sPostFragment=preg_replace('/\[\/?[a-z\*\:]*\?/', '', $sPostFragment);
					
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
					echo '<a href="/communicatie/forum/onderwerp/'.$aZoekResultaat['tid'].'/'.urlencode($sZoekQuery).'#post'.$aZoekResultaat['postID'].'">';
					echo $aZoekResultaat['titel'].'</a>';
					if($aZoekResultaat['aantal']!=1){ echo ' <em>('.$aZoekResultaat['aantal'].' berichten in dit onderwerp)</em>'; }
					echo '<br />'.$sPostFragment.'</td>';
					echo '<td class="forumtitel">'.$this->_forum->getForumNaam($aZoekResultaat['uid'],$aZoekResultaat).'</td>';
					echo '<td class="forumtitel">
						<a href="/communicatie/forum/categorie/'.$aZoekResultaat['categorie'].'">'.$aZoekResultaat['categorieTitel'].'</a></td>';
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
		echo '<form action="/communicatie/forum/zoeken.php" method="post"><p><input type="text" value="'.$sZoekQuery.'" name="zoeken" />';
		echo '<input type="submit" value="zoeken" name="verzenden" /></p></form><br />';
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
			$sTitel='<a href="/communicatie/forum/">Forum</a> &raquo; <a href="/communicatie/forum/categorie/'.$iCategorieID.'">'.$sCategorie.'</a> &raquo; '.$this->_forum->getTopicTitel($iTopicID);
		}elseif($this->_actie=='forum' AND isset($_GET['forum'])){
			$sTitel='<a href="/communicatie/forum/">Forum</a> &raquo; ';
			if($_GET['forum']==0){
				$sTitel.='Laatste forumberichten';
			}else{
				 $sTitel.=$this->_forum->getCategorieTitel((int)$_GET['forum']);
			}
		}elseif($this->_actie=='zoeken'){
			$sTitel='<a href="/communicatie/forum/">Forum</a> &raquo; zoeken';
		}else{
			$sTitel='Forum';
		}
		echo $sTitel;
	}
	function getTitel(){
		$sTitel='Forum - ';
		if(($this->_actie=='topic' AND isset($_GET['topic'])) OR 
				($this->_actie=='citeren' AND isset($_GET['post'])) ){
			if(isset($_GET['topic'])){
				$iTopicID=(int)$_GET['topic'];
			}else{
				$iTopicID=$this->_forum->getTopicVoorPostID((int)$_GET['post']);
			}
			$sCategorie=$this->_forum->getCategorieTitel($this->_forum->getCategorieVoorTopic($iTopicID));
			$sTitel.=$sCategorie.' - '.$this->_forum->getTopicTitel($iTopicID);
		}elseif($this->_actie=='forum' AND isset($_GET['forum'])){
			if($_GET['forum']!=0){
				$sTitel.=$this->_forum->getCategorieTitel((int)$_GET['forum']);
			}else{
				$sTitel.='Laatste forumberichten';
			}
		}elseif($this->_actie=='zoeken'){
			$sTitel.='zoeken';
		}else{
			$sTitel='Forum';
		}
		return $sTitel; 
	}
	function view(){
		switch($this->_actie){
			case 'forum': 
				if(isset($_GET['forum'])){ 
					$this->viewTopics((int)$_GET['forum']); 
				}else{
					$this->viewCategories(); 
				}
			break;
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
					$this->viewTopic((int)$_GET['post']); 
				}else{ 
					$this->viewCategories(); 
				} 
			break;
			case 'rss': $this->rssFeed();	break;
			case 'lastposts': $this->lastPosts(); break;
			case 'zoeken': $this->zoeken(); break;
			default: $this->viewCategories();	break;
		}
	}
}
?>
