<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forum.php
# -------------------------------------------------------------------

/*
 * class Forum
 * een verzameling van functies die niet direct bij een object van het forum horen
 *
 */
class Forum{
	//het aantal posts voor een rss feed
	private static $_postsPerRss=15;
	//aantal zoekresultaten
	private static $_aantalZoekResultaten=40;

	public static function getTopicVoorPostID($iPostID){
		$iTopicInfo = array();
		$iTopicInfo['tid'] = 0;
		$iTopicInfo['pagina'] = 1;

		$db=MySql::instance();
		$iPostID=(int)$iPostID;
		$sPostQuery="
			SELECT tid
			FROM forum_post
			WHERE id=".$iPostID."
			LIMIT 1;";
		$post=$db->getRow($sPostQuery);
		if(is_array($post)){
			$iTopicInfo['tid'] = $post['tid'];

			$zichtBaarClause="post.zichtbaar='zichtbaar'";
			if(Forum::isModerator()){
				$zichtBaarClause.=" OR post.zichtbaar='wacht_goedkeuring' OR post.zichtbaar='spam'";
			}
			$sPostQuery="
				SELECT count(*) as pagina
				FROM forum_post as post
				WHERE tid=".$post['tid']."
				AND datum <= (
					SELECT datum
					FROM forum_post
					WHERE id=".$iPostID."
				)
				AND ( ".$zichtBaarClause." )
				LIMIT 1;";
			$postpagina=$db->getRow($sPostQuery);
			if(is_array($postpagina)){
				$pagina=ceil($postpagina['pagina']/Forum::getPostsPerPagina());
				if($pagina>0){
					$iTopicInfo['pagina'] = $pagina;
				}
			}
		}
		return $iTopicInfo;
	}

	private function getCategorieClause($token=null){
		$lid=LoginLid::instance();
		//uitmaken welke categorieën er in de rss feed komen. Voor feut (bot in #csrdelft)
		//is er een uitzondering op de ingeloggedheid.

		//extern, zandbak, vraag en aanbod en kamers worden altijd weergegeven.
		$cats=array(2,4,11,12);

		$perm='P_LEDEN_READ';
		if($lid->hasPermission($perm) OR isFeut() OR $lid->validateWithToken($token, $perm)){
			//C.S.R.-zaken, webstek terugkoppeling, geloofszaken, nieuws&actualiteit, electronica en techniek,
			//groeperingen, kringen& werkgroepen, bidpunten, vacatures
			$cats=array_merge($cats, array(1, 3, 10, 9, 13, 17, 18, 20, 21));
		}
		$perm='P_OUDLEDEN_READ';
		if($lid->hasPermission($perm) OR isFeut() OR $lid->validateWithToken($token, $perm)){
			//oudledenforum
			$cats[]=8;
		}
		$perm='P_FORUM_MOD';
		if($lid->hasPermission($perm) OR $lid->validateWithToken($token, $perm)){
			//pubcie-forum enkel voor forummods.
			$cats[]=6;
		}
		//aan elkaar plakken:
		foreach($cats as $cat){
			$sCats[]='topic.categorie='.$cat;
		}
		return implode(' OR ', $sCats);

	}
	public static function getPostsVoorRss($iAantal=false, $bDistinct=true, $token=null, $uid=null){
		if($iAantal===false){
			$iAantal=Forum::$_postsPerRss;
		}
		$sDistinctClause=' AND 1';
		if($bDistinct){
			$sDistinctClause='AND topic.lastpostID=post.id';
		}
		$uidClause=' AND 1';
		if($uid!=null){
			$uidClause=" AND post.uid='".$uid."'";
		}

		//zoo, uberdeuberdeuber query om een topic op te halen. Namen worden
		//ook opgehaald in deze query, die worden door forumcontent weer
		//doorgegeven aan getForumNaam();
		$query="
			SELECT
				topic.id AS tid,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
					categorie.titel AS categorieTitel,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				topic.lastpost AS lastpost,
				topic.reacties AS reacties,
				post.uid AS uid,
				post.id AS postID,
				post.tekst AS tekst,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum
			FROM
				forum_topic topic
			INNER JOIN
				forum_cat categorie ON(categorie.id=topic.categorie)
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			WHERE
				topic.zichtbaar='zichtbaar' AND
				post.zichtbaar='zichtbaar' AND
				( ".Forum::getCategorieClause($token)." )
				".$sDistinctClause." ".$uidClause."
			ORDER BY
				post.datum DESC
			LIMIT
				".$iAantal.";";
		return MySql::instance()->query2array($query);
	}
	public static function isIngelogged(){ return LoginLid::instance()->hasPermission('P_LOGGED_IN'); }
	public static function isModerator(){ return LoginLid::instance()->hasPermission('P_FORUM_MOD'); }
	public static function getLaatstBekeken(){ return LoginLid::instance()->getForumLaatstBekeken(); }
	public static function updateLaatstBekeken(){ return LoginLid::instance()->updateForumLaatstBekeken(); }

	public static function getTopicsPerPagina(){ return Instelling::get('forum_onderwerpenPerPagina'); }
	public static function getPostsPerPagina(){ return Instelling::get('forum_postsPerPagina'); }
	
	public static function getForumNaam($uid=false, $aNaam=false, $aLink=true, $bHtmlentities=true ){
		return LidCache::getLid($uid)->getNaamLink('user', ($aLink ? 'link' : 'html'));
	}


	public static function getPostsVoorUid($uid=null, $aantal=false){
		if($uid==null){ LoginLid::instance()->getUid(); }
		return Forum::getPostsVoorRss($aantal, false, null, $uid);
	}
	public static function getUserPostCount($uid=null){
		if($uid==null){ LoginLid::instance()->getUid(); }
		$db=MySql::instance();
		$query="
			SELECT count(*) AS aantal
			FROM forum_post as post
			INNER JOIN forum_topic as onderwerp ON(post.tid=onderwerp.id)
			INNER JOIN forum_cat as categorie ON(onderwerp.categorie=categorie.id)
			WHERE post.uid='".$uid."'
			  AND post.zichtbaar='zichtbaar' AND categorie.id!=6;";
		
		$data=$db->getRow($query);
		if(is_array($data)){
			return $data['aantal'];
		}else{
			return 0;
		}
	}
	public static function searchPosts($sZoekQuery, $categorie=null){
		if(!preg_match('/^[a-zA-Z0-9 \-\+\'\"\.]*$/', $sZoekQuery)){
			return false;
		}
		$db=MySql::instance();

		$sZoekQuery=$db->escape(trim($sZoekQuery));

		$singleCat='1';
		if($categorie!==null AND $categorie!=0){
			foreach(ForumCategorie::getAll(true) as $cat){
				if($cat['id']==$categorie){
					$singleCat='topic.categorie='.(int)$categorie;
				}
			}
		}

		//zoo, uberdeuberdeuber query om een topic op te halen.
		$sSearchQuery="
			SELECT
				topic.id AS tid,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
					cat.titel AS categorieTitel,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				post.uid AS uid,
				post.id AS postID,
				post.tekst AS tekst,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum,
				count(*) AS aantal
			FROM
				forum_post post
			INNER JOIN
				forum_topic topic ON( post.tid=topic.id )
			INNER JOIN
				forum_cat cat ON( topic.categorie=cat.id )
			WHERE
				topic.zichtbaar='zichtbaar' AND post.zichtbaar='zichtbaar' AND
				( ".Forum::getCategorieClause()." ) AND (".$singleCat.") AND
				MATCH(post.tekst, topic.titel )AGAINST( '".$sZoekQuery."' IN BOOLEAN MODE )
			GROUP BY
				topic.id
			ORDER BY
				post.datum DESC
			LIMIT
				".Instelling::get('forum_zoekresultaten').";";
		return $db->query2array($sSearchQuery);
	}
}
?>
