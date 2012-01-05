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

	private function getCategorieClause(){
		$loginlid=LoginLid::instance();

		$cats=array();
		foreach(ForumCategorie::getAll() as $cat){
			if($loginlid->hasPermission($cat['rechten_read'], $token_authorizable=true)){
				$cats[]='topic.categorie='.$cat['id'];
			}

		}

		return implode(' OR ', $cats);
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
				topic.lastpost AS lastpost,
				topic.reacties AS reacties,
				post.uid AS uid,
				post.id AS postID,
				post.tekst AS tekst,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum,
				gelezen.moment AS momentGelezen
			FROM
				forum_topic topic
			INNER JOIN
				forum_cat categorie ON(categorie.id=topic.categorie)
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			LEFT JOIN
				forum_gelezen AS gelezen
			ON
				gelezen.tid = topic.id AND
				gelezen.uid = '".LoginLid::instance()->getUid()."'
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

	public static function getPostsZijbalkBelangrijk($iAantal=false, $bDistinct=true, $token=null, $uid=null){
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
				topic.lastpost AS lastpost,
				topic.reacties AS reacties,
				post.uid AS uid,
				post.id AS postID,
				post.tekst AS tekst,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum,
				gelezen.moment AS momentGelezen
			FROM
				forum_topic topic
			INNER JOIN
				forum_cat categorie ON(categorie.id=topic.categorie)
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			LEFT JOIN
				forum_gelezen AS gelezen
			ON
				gelezen.tid = topic.id AND
				gelezen.uid = '".LoginLid::instance()->getUid()."'
			WHERE
				topic.belangrijk = '1' AND
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

	public static function getTopicsPerPagina(){ return Instelling::get('forum_onderwerpenPerPagina'); }
	public static function getPostsPerPagina(){ return Instelling::get('forum_postsPerPagina'); }

	public static function getForumNaam($uid=false, $aNaam=false, $aLink=true, $bHtmlentities=true ){
		return LidCache::getLid($uid)->getNaamLink('user', ($aLink ? 'link' : 'html'));
	}


	public static function getPostsVoorUid($uid=null, $aantal=false, $bDistinct=false){
		if($uid==null){ LoginLid::instance()->getUid(); }
		return Forum::getPostsVoorRss($aantal, $bDistinct, null, $uid);
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
	public static function searchPosts($query, $categorie=null){
		$db=MySql::instance();

		if(!preg_match('/^[a-zA-Z0-9 \-\+\'\"\.]*$/', $query)){
			return false;
		}
		$query=$db->escape(trim($query));

		$singleCat='1';
		if($categorie!==null AND $categorie!=0){
			foreach(ForumCategorie::getAll(true) as $cat){
				if($cat['id']==$categorie){
					$singleCat='topic.categorie='.(int)$categorie;
				}
			}
		}

		$dbQuery="
			SELECT
				topic.id AS tid,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
				cat.titel AS categorieTitel,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
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
			WHERE topic.zichtbaar='zichtbaar'
			  AND post.zichtbaar='zichtbaar'
			  AND (".Forum::getCategorieClause().")
			  AND (".$singleCat.")
			  AND (
				  MATCH(post.tekst)AGAINST('".$query."' IN BOOLEAN MODE ) OR
				  topic.titel LIKE '%".$query."%'
				)
			GROUP BY
				topic.id
			ORDER BY
				post.datum DESC
			LIMIT
				".Instelling::get('forum_zoekresultaten').";";
		//Als MySQL 5.1.7 op syrinx staat kan er in 'natural language mode' gezocht worden
		//MATCH(post.tekst)AGAINST('".$query."' IN NATURAL LANGUAGE MODE ) OR

		return $db->query2array($dbQuery);
	}
}
?>
