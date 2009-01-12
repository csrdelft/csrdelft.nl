<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forum.php
# -------------------------------------------------------------------
# Forum databaseklasse
# -------------------------------------------------------------------


class Forum {

	protected $_db;
	protected $_lid;

	//het aantal topics per pagina in het overzicht per categorie
	//de standaard, het kan wellicht nog een keer in het profiel gerost worden.
	private static $_topicsPerPagina=15;

	//het aantal posts voor een rss feed
	private static $_postsPerRss=15;

	//aantal zoekresultaten
	private static $_aantalZoekResultaten=40;

	//constructor.
	public function __construct(){
		$this->_lid=Lid::instance();
		$this->_db=MySql::instance();
	}
/***************************************************************************************************
*	Lijsten: Categorieën, posts, topics en enkele posts ophalen.
*
***************************************************************************************************/
	//categorieen gesorteerd op volgorde
	function getCategories($voorLid=false){
		$sCatsQuery="
			SELECT
				id, titel, beschrijving, lastuser, lastpost, lasttopic, lastpostID, reacties, topics, rechten_read
			FROM
				forum_cat
			WHERE
				zichtbaar=1
			ORDER BY
				volgorde;";
		$rCatsResult=$this->_db->query($sCatsQuery);

		while($aCat=$this->_db->next($rCatsResult)){
			if($voorLid===true AND !$this->_lid->hasPermission($aCat['rechten_read'])){
				continue;
			}
			$aCats[]=$aCat;
		}
		return $aCats;
	}
	/*
	* Topicoverzicht binnehalen, gesorteerd op plakkerig, lastpost.
	* Eventueel nog paginering.
	*/
	function getTopics($iCat, $iPagina=1){
		$iCat=(int)$iCat;
		$iPagina=$iPagina-1;
		//ook op bevestiging wachtende berichten van niet ingelogde gebruikers zichtbaar maken
		//voor moderators
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			$zichtBaarClause="( topic.zichtbaar='zichtbaar' OR topic.zichtbaar='wacht_goedkeuring' )";
		}else{
			$zichtBaarClause="topic.zichtbaar='zichtbaar'";
		}
		$sTopicsQuery="
			SELECT
				categorie.titel AS categorieTitel,
				categorie.rechten_read AS rechten_read,
				categorie.rechten_post AS rechten_post,
				topic.id AS id,
				topic.titel AS titel,
				topic.uid AS uid, topic.datumtijd AS datumtijd,
				topic.lastuser AS lastuser, topic.lastpost AS lastpost,
				topic.lastpostID AS lastpostID, topic.reacties AS reacties,
				topic.plakkerig AS plakkerig, topic.open AS open, topic.soort AS soort,
				topic.zichtbaar AS zichtbaar
			FROM
				forum_topic topic
			INNER JOIN
				forum_cat categorie ON (categorie.id=topic.categorie)
			WHERE
				topic.categorie=".$iCat."
			AND
				".$zichtBaarClause."
			ORDER BY
				topic.plakkerig,
				topic.lastpost DESC
			LIMIT
				".($iPagina*$this->getTopicsPerPagina()).", ".$this->getTopicsPerPagina().";";
		$rTopicsResult=$this->_db->query($sTopicsQuery);
		return $this->_db->result2array($rTopicsResult);
	}

	private function getCategorieClause(){
		//uitmaken welke categorieën er in de rss feed komen. Voor feut (bot in #csrdelft)
		//is er een uitzondering op de ingeloggedheid.

		//extern, zandbak, vraag en aanbod en kamers worden altijd weergegeven.
		$cats=array(2,4,11,12);

		if($this->_lid->hasPermission('P_LEDEN_READ') OR isFeut()){
			//C.S.R.-zaken, webstek terugkoppeling, geloofszaken, nieuws&actualiteit, electronica en techniek,
			//groeperingen, kringen& werkgroepen, bidpunten, vacatures
			$cats=array_merge($cats, array(1, 3, 10, 9, 13, 17, 18, 20, 21));
		}
		if($this->_lid->hasPermission('P_OUDLEDEN_READ') OR isFeut()){
			//oudledenforum
			$cats[]=8;
		}
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			//pubcie-forum enkel voor forummods.
			$cats[]=6;
		}
		//aan elkaar plakken:
		foreach($cats as $cat){
			$sCats[]='topic.categorie='.$cat;
		}
		return implode(' OR ', $sCats);

	}

	//laatste posts voor heel het forum.
	function getPostsVoorRss($iAantal=false, $bDistinct=true){
		if($iAantal===false){
			$iAantal=Forum::$_postsPerRss;
		}
		$sDistinctClause=' AND 1';
		if($bDistinct){
			$sDistinctClause='AND topic.lastpostID=post.id';
		}

		//zoo, uberdeuberdeuber query om een topic op te halen. Namen worden
		//ook opgehaald in deze query, die worden door forumcontent weer
		//doorgegeven aan getForumNaam();
		$sPostsQuery="
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
				( ".$this->getCategorieClause()." )
				".$sDistinctClause."
			ORDER BY
				post.datum DESC
			LIMIT
				".$iAantal.";";
		$rPostsResult=$this->_db->query($sPostsQuery);
		return $this->_db->result2array($rPostsResult);
	}
/***************************************************************************************************
*	Dingen opslaan, bewerken en verwijderen: nieuwe posts en topics, posts bewerken
*
***************************************************************************************************/

/***************************************************************************************************
*	Dingen uitrekenen: post naar topic id, topic naar cat id
*
***************************************************************************************************/
	// categorie id voor een topic
	function getCategorieVoorTopic($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			SELECT
				categorie
			FROM
				forum_topic
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		$rTopic=$this->_db->query($sTopicQuery);
		if($this->_db->numRows($rTopic)==1){
			$aTopic=$this->_db->next($rTopic);
			return $aTopic['categorie'];
		}else{
			return false;
		}
	}
	// topic id voor post
	public static function getTopicVoorPostID($iPostID){
		$db=MySql::instance();
		$iPostID=(int)$iPostID;
		$sPostQuery="
			SELECT
				tid
			FROM
				forum_post
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		$rPost=$db->query($sPostQuery);
		if($db->numRows($rPost)==1){
			$aPost=$db->next($rPost);
			return $aPost['tid'];
		}else{
			return false;
		}
	}
/***************************************************************************************************
*	Vragen over categorieen
*
***************************************************************************************************/
	//bestaat een categorie?
	public static function catExistsVoorUser($iCatID){
		$db=MySql::instance();
		$iCatID=(int)$iCatID;
		$sCatQuery="
			SELECT
				rechten_read
			FROM
				forum_cat
			WHERE
				id=".$iCatID."
			LIMIT 1;";
		$rCat=$db->query($sCatQuery);
		if($db->numRows($rCat)==1){
			$aCat=$db->next($rCat);
			return Lid::instance()->hasPermission($aCat['rechten_read']);
		}else{
			return false;
		}
	}

	// enkel een string met de topictitel
	function getCategorieTitel($iCatID){
		$iCatID=(int)$iCatID;
		$sCatQuery="
			SELECT
				titel
			FROM
				forum_cat
			WHERE
				id=".$iCatID."
			LIMIT 1;";
		$rCat=$this->_db->query($sCatQuery);
		if($this->_db->numRows($rCat)==1){
			$aCat=$this->_db->next($rCat);
			return $aCat['titel'];
		}else{
			return false;
		}
	}
	//aantal pagina's in een categorie uitrekenen:
	function getPaginaCount($iCatID){
		$iCatID=(int)$iCatID;
		$sCatQuery="
			SELECT
				count(*) as aantal
			FROM
				forum_topic
			WHERE
				categorie=".$iCatID."
			LIMIT 1;";
		$rCat=$this->_db->query($sCatQuery);
		if($this->_db->numRows($rCat)==1){
			$aCat=$this->_db->next($rCat);
			return ceil($aCat['aantal']/$this->_topicsPerPagina);
		}else{
			return 1;
		}
	}
	//rechten voor een categorie ophaelen
	function getRechten_read($iCatID){ return $this->_getRechten($iCatID, 'read'); }
	function getRechten_post($iCatID){ return $this->_getRechten($iCatID, 'post'); }
	function _getRechten($iCatID, $gebied){
		$iCatID=(int)$iCatID;
		$sCatQuery="
			SELECT
				rechten_".$gebied." as rechten
			FROM
				forum_cat
			WHERE
				id=".$iCatID."
			LIMIT 1;";
		$rCat=$this->_db->query($sCatQuery);
		if($this->_db->numRows($rCat)==1){
			$aCat=$this->_db->next($rCat);
			return $aCat['rechten'];
		}else{
			return false;
		}
	}
/***************************************************************************************************
*	Vragen over topics
*
***************************************************************************************************/
	//aantal topics in een categorie
	function topicCount($iCat){
		$iCat=(int)$iCat;
		$sTopicsQuery="
			SELECT
				count(*) AS aantal
			FROM
				forum_topic
			WHERE
				categorie=".$iCat."
			LIMIT 1;";
		$rTopicsResult=$this->_db->query($sTopicsQuery);
		if($this->_db->numRows($rTopicsResult)==1){
			$aTopics=$this->_db->next($rTopicsResult);
			return $aTopics['aantal'];
		}else{
			return false;
		}
	}
	//enkel een string met de topictitel
	function getTopicTitel($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			SELECT
				titel
			FROM
				forum_topic
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		$rTopic=$this->_db->query($sTopicQuery);
		if($this->_db->numRows($rTopic)==1){
			$aTopic=$this->_db->next($rTopic);
			return $aTopic['titel'];
		}else{
			return false;
		}
	}

/***************************************************************************************************
*	Updaten van stats in categorie en topic
*
***************************************************************************************************/


	//dingen updaten voor de categorie.
	function updateCatStats($iCatID){
		$sCatStats="
			SELECT
				id, lastuser, lastpostID, lastpost
			FROM
				forum_topic
			WHERE
				categorie=".$iCatID." AND
				zichtbaar='zichtbaar'
			ORDER BY
				lastpost DESC
			LIMIT 1;";
		$rCatStats=$this->_db->query($sCatStats);
		if($this->_db->numRows($rCatStats)==1){
			$aCatStats=$this->_db->next($rCatStats);
			//subqueries voor aantal reacties en aantal topics
			$reacties="(SELECT SUM(reacties) AS aantal FROM forum_topic WHERE categorie=".$iCatID." GROUP BY categorie LIMIT 1)";
			$topics="(SELECT count(*) AS aantal FROM forum_topic WHERE categorie=".$iCatID." LIMIT 1)";
		}else{
			$aCatStats['id']=0;
			$aCatStats['lastpostID']=0;
			$aCatStats['lastuser']=0;
			$aCatStats['lastpost']='0000-00-00 00:00:00';
			$reacties=0;
			$topics=0;
		}
		$sCatUpdate="
			UPDATE
				forum_cat
			SET
				lasttopic=".$aCatStats['id'].",
				lastpostID=".$aCatStats['lastpostID'].",
				lastuser='".$aCatStats['lastuser']."',
				lastpost='".$aCatStats['lastpost']."',
				reacties=".$reacties.",
				topics=".$topics."
			WHERE
				id=".$iCatID."
			LIMIT 1;";
		return $this->_db->query($sCatUpdate);

	}


/***************************************************************************************************
*	Zoeken.
*
***************************************************************************************************/
	function searchPosts($sZoekQuery){
		if(preg_match('/^[a-zA-Z0-9 \-\+\'\"\.]*$/', $sZoekQuery)){

		//sZoekQuery controleren:
		$sZoekQuery=$this->_db->escape(trim($sZoekQuery));

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
				( ".$this->getCategorieClause()." ) AND
				MATCH(post.tekst, topic.titel )AGAINST( '".$sZoekQuery."' IN BOOLEAN MODE )
			GROUP BY
				topic.id
			ORDER BY
				post.datum DESC
			LIMIT
				".Forum::$_aantalZoekResultaten.";";
			$rSearchResult=$this->_db->query($sSearchQuery);
			return $this->_db->result2array($rSearchResult);
		}else{
			return false;
		}
	}
	function getForumNaam($uid=false, $aNaam=false, $aLink=true, $bHtmlentities=true ){
		return $this->_lid->getNaamLink($uid, 'user', $aLink, $aNaam, $bHtmlentities);
	}

	function getTopicsPerPagina(){ return Forum::$_topicsPerPagina; }
	function isIngelogged(){ return Lid::instance()->hasPermission('P_LOGGED_IN'); }
	function isModerator(){ return Lid::instance()->hasPermission('P_FORUM_MOD'); }
	function getLaatstBekeken(){ return Lid::instance()->getForumLaatstBekeken(); }
	function updateLaatstBekeken(){ Lid::instance()->updateForumLaatstBekeken(); }
}//einde classe Forum
?>
