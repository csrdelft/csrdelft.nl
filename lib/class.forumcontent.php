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
		$smarty=new Smarty_csr();
		$smarty->assign('categories',$this->_forum->getCategories(true));
		$smarty->assign('melding', $this->getMelding());
		$smarty->display('forum/list_categories.tpl');
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
			$iPaginaID=1; 
		}
		if($this->_forum->catExistsVoorUser($iCat)){
			$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			//als de pagina niet bestaat moet er teruggegaan worden naar de laatste pagina.
			if($iPaginaID!=0 AND $aTopics===false){
				//de pagina die opgevraagd wordt bestaat niet, gewoon maar de eerste weergeven dan.
				$iPaginaID=1;
				$aTopics=$this->_forum->getTopics($iCat, $iPaginaID);
			}
			$iAantalTopics=$this->_forum->topicCount($iCat);
			$template='forum/list_onderwerpen.tpl';
		}elseif($iCat==0){
			$this->_topicsPerPagina=40;
			$aTopics=$this->_forum->getPostsVoorRss($this->_topicsPerPagina);
			$iAantalTopics=count($aTopics);
			$template='forum/list_recent.tpl';
		}else{
			echo '<h2><a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; Foutje</h2>Dit gedeelte van het forum is niet zichtbaar voor u, of het bestaat &uuml;berhaupt niet.
				<a href="/communicatie/forum/">Terug naar het forum</a>';
			return;
		}
		
		$smarty=new Smarty_csr();
		$smarty->assign('categorie', $iCat);
		$smarty->assign('categorietitel', $this->_forum->getCategorieTitel($iCat));
		$smarty->assign('berichten', $aTopics);
		
		//paginanummertjes
		$pagina['baseurl']='/communicatie/forum/categorie/'.$iCat.'/';
		$pagina['aantal']=1;
		if($iAantalTopics>$this->_topicsPerPagina){
			$pagina['aantal']=ceil($iAantalTopics/$this->_topicsPerPagina);
		}
		$pagina['huidig']=$iPaginaID;
		$smarty->assign('pagina', $pagina);
		
		$lid=Lid::get_Lid();
		//TODO: dit netjes fixen.
		$smarty->assign('magPosten', $lid->hasPermission($aTopics[0]['rechten_post']));
		$smarty->assign('melding', $this->getMelding());
		$smarty->display($template);
		
		
		
	
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
					$this->_forum->getForumNaam($aPost['uid'], $aPost, false).': '.mb_htmlentities($postfragment).'"';
			if (strtotime($aPost['datum']) > $this->_forum->getLaatstBekeken()) { echo ' class="opvallend"'; }
			echo '>'.$tekst.'</a><br />'."\n";
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
