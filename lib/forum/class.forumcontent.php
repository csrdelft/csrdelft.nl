<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumcontent.php
# -------------------------------------------------------------------

require_once 'class.simplehtml.php';
require_once 'forum/class.forum.php';
require_once 'forum/class.forumcategorie.php';

class ForumContent extends SimpleHTML {
	private $forum;
	private $actie;
	private $sTitel='forum';

	function __construct($actie){
		$this->actie=$actie;
	}
/***********************************************************************************************************
* Overzicht van Categorieën met aantal topics en posts
*
***********************************************************************************************************/
	function viewCategories(){
		$smarty=new Smarty_csr();
		$smarty->assign('categories', ForumCategorie::getAll(true));
		$smarty->assign('melding', $this->getMelding());
		$smarty->display('forum/list_categories.tpl');
	}


/***********************************************************************************************************
* rss feed weergeven van het forum.
*
***********************************************************************************************************/
	function rssFeed(){
		$token='';
		if(isset($_GET['token']) AND preg_match('/[a-z0-9]{15}/', $_GET['token'])){
			$token=$_GET['token'];
		}
		$aPosts=Forum::getPostsVoorRss(false, false, $token);

		$rss=new Smarty_csr();
		$rss->assign('aPosts', $aPosts);

		$rss->display('forum/rss.tpl');
	}
	public function lastPostsZijbalk($zelf=false){
		if($zelf){
			$uid=LoginLid::instance()->getUid();
			$aPosts=Forum::getPostsVoorUid($uid, Instelling::get('zijbalk_forum_zelf'));
			echo '<h1><a href="/communicatie/profiel/'.$uid.'/#forum">Forum (zelf gepost)</a></h1>';
		}else{
			$aPosts=Forum::getPostsVoorRss(Instelling::get('zijbalk_forum'), true);
			echo '<h1><a href="/communicatie/forum/categorie/laatste">Forum</a></h1>';
		}
		
		if(!is_array($aPosts)){
			echo '<div class="item">Geen items gevonden</div>';
		}else{
			foreach($aPosts as $aPost){
				$tekst=$aPost['titel'];
				if(strlen($tekst)>20){
					$tekst=trim(substr($tekst, 0, 18)).'…';
				}
				$tekst=mb_htmlentities($tekst);
				$tekst=str_replace(' ', '&nbsp;', $tekst);

				$post=preg_replace('/(\[(|\/)\w+\])/', '|', $aPost['tekst']);
				$postfragment=substr(str_replace(array("\n", "\r", ' '), ' ', $post), 0, 40);
				echo '<div class="item"><span class="tijd">'.date('H:i', strtotime($aPost['datum'])).'</span>&nbsp;';
				echo '<a href="/communicatie/forum/reactie/'.$aPost['postID'].'"
					title="['.htmlspecialchars($aPost['titel']).'] '.
						Forum::getForumNaam($aPost['uid'], false, false).': '.mb_htmlentities($postfragment).'"';
				if(strtotime($aPost['datum']) > Forum::getLaatstBekeken()) { echo ' class="opvallend"'; }
				echo '>'.$tekst.'</a><br />'."\n";
				echo '</div>';
			}
		}
	}
	public function lastPosts(){
 		$smarty=new Smarty_csr();
		$smarty->assign('berichten', Forum::getPostsVoorRss(Instelling::get('forum_zoekresultaten')));
		$smarty->assign('melding', $this->getMelding());
		$smarty->display('forum/list_recent.tpl');
	}

	public function zoeken(){
		$sZoekQuery='';
		if(isset($_POST['zoeken'])){ $sZoekQuery=trim($_POST['zoeken']); }elseif(isset($_GET['zoeken'])){ $sZoekQuery=trim($_GET['zoeken']);}

		echo '<div class="zoekhulp"><h2>Bewerkingstekens</h2>
			<table><tr><td>+</td><td>= en</td></tr>
		          <tr><td>-</td><td>= niet</td></tr>
		          <tr><td><em>spatie</em></td><td>= of</td></tr>
		          <tr><td><b>"</b>zoek zin<b>" <b> </td><td>= zoekt exact <em>zoek zin</em></td></tr>
		          <tr><td>*</td><td>= wildcard</td></tr></table>
		          <a href="http://dev.mysql.com/doc/refman/5.0/en/fulltext-boolean.html">Verdere uitleg booleaans zoeken MySQL...</a></div>';
		echo '<h1>Zoeken in het forum</h1>Hier kunt u zoeken in het forum.<br />';
		//altijd het zoekformulier weergeven.

		$this->zoekFormulier($sZoekQuery, (int)$_POST['categorie']);
		if($sZoekQuery!=''){
			$aZoekResultaten=Forum::searchPosts($sZoekQuery, (int)$_POST['categorie']);
			if(is_array($aZoekResultaten)){
				$aZoekOnderdelen=explode(' ', $sZoekQuery);
				$sEersteTerm=$aZoekOnderdelen[0];
				echo 'In <em>'.count($aZoekResultaten).'</em> onderwerpen kwam de volgende zoekterm voor: <strong>'.mb_htmlentities($sZoekQuery).'</strong>';
				echo '<br /><br /><table id="forumtabel"><tr><th>Onderwerp</th><th>Auteur</th>';
				echo '<th>categorie</th><th>datum</th></tr>';
				$row=0;
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

					echo '<tr class="kleur'.($row%2).'"><td class="forumtitel">';
					echo '<a href="/communicatie/forum/reactie/'.$aZoekResultaat['postID'].'">';
					echo $aZoekResultaat['titel'].'</a>';
					if($aZoekResultaat['aantal']!=1){ echo ' <em>('.$aZoekResultaat['aantal'].' berichten in dit onderwerp)</em>'; }
					echo '<br />'.$sPostFragment.'</td>';
					echo '<td class="titel">'.Forum::getForumNaam($aZoekResultaat['uid']).'</td>';
					echo '<td class="titel">
						<a href="/communicatie/forum/categorie/'.$aZoekResultaat['categorie'].'">'.$aZoekResultaat['categorieTitel'].'</a></td>';
					echo '<td class="titel">
						'.$aZoekResultaat['datum'].'</td>';
					echo '</tr>';
					$row++;
				}
				echo '</table>';
			}else{
				echo '<h3>Er is niets gevonden</h3>';
				if((int)$_POST['categorie']!=0){
					echo 'Er is niets gevonden in deze categorie. ';
				}
				echo 'Pas uw zoekterm aan. (Zoekresultaten moeten minimaal 4 letters bevatten)';

			}
		}
	}
	function zoekFormulier($sZoekQuery='', $selectedCat=0){
		require_once 'forum/class.forumcategorie.php';
		$sZoekQuery=htmlspecialchars($sZoekQuery, ENT_QUOTES, 'UTF-8');
		echo '<form action="/communicatie/forum/zoeken.php" method="post">';
		echo '<p><input type="text" value="'.$sZoekQuery.'" name="zoeken" />&nbsp;';
		echo 'in categorie: <select name="categorie"><option value="0">Alle</option>';
		foreach(ForumCategorie::getAll(true) as $cat){
			if($cat['titel']!='SEPARATOR'){
				echo '<option value="'.$cat['id'].'"';
				if($cat['id']==$selectedCat){ echo ' selected="selected" '; }
				echo '>'.$cat['titel'].'</option>';
			}
		}
		echo '</select>&nbsp;';
		echo '<input type="submit" value="zoeken" name="verzenden" /></p></form><br />';
	}
	function getTitel(){
		$sTitel='Forum - ';
		if($this->actie=='zoeken'){
			$sTitel.='zoeken';
		}else{
			$sTitel='Forum';
		}
		return $sTitel;
	}
	function view(){
		switch($this->actie){
			case 'recent': $this->lastPosts(); break;
			case 'rss': $this->rssFeed();	break;
			case 'lastposts': $this->lastPostsZijbalk(); break;
			case 'lastposts_zelf': $this->lastPostsZijbalk(true); break;
			case 'zoeken': $this->zoeken(); break;
			default: $this->viewCategories();	break;
		}
	}
}
?>
