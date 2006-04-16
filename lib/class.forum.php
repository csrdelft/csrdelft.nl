<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.forum.php
# -------------------------------------------------------------------
# Forum databaseklasse
# -------------------------------------------------------------------
# Historie:
# 27-01-2006 - Jieter
# . gemaakt
#



class Forum {
	
	var $_db;
	var $_lid;
	
	//start-tijd van het begin van het parsen van het forum.
	//gebruikt om aan het einde de totale parse-tijd van het forum te berekenen.
	var $_parseStart;
	
	//array waarin namen gecached worden bij het opvragen van namen,
	//zodat elke naam maar één keer uit de database hoeft te komen
	var $_forumNaamCache;
	
	//het aantal topics per pagina in het overzicht van categorieën
	//de standaard, het kan wellicht nog een keer in het profiel gerost worden.
	var $_topicsPerPagina=15;
	
	//het aantal posts voor een rss feed
	var $_postsPerRss=15;
	
	//constructor.
	function Forum(&$lid, &$db){
		$this->_lid =& $lid;
		$this->_db =& $db;
		
		//starttijd in parseStart rossen
		list($usec, $sec) = explode(" ",microtime()); 
   	$this->_parseStart=((float)$usec + (float)$sec); 
	}
/***************************************************************************************************
*	Lijsten: Categorieën, posts, topics en enkele posts ophalen.
*
***************************************************************************************************/
	//categorieen gesorteerd op volgorde
	function getCategories(){
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
		return $this->_db->result2array($rCatsResult);
	}
	/*
	* Topicoverzicht binnehalen, gesorteerd op plakkerig, lastpost.
	* Eventueel nog paginering.
	*/
	function getTopics($iCat, $iPagina=0){
		$iCat=(int)$iCat;
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
				".($iPagina*$this->_topicsPerPagina).", ".$this->_topicsPerPagina.";";
		$rTopicsResult=$this->_db->query($sTopicsQuery);
		return $this->_db->result2array($rTopicsResult); 
	}
	//posts voor topic, gesorteerd op datum
	function getPosts($iTopicID){
		$iTopicID=(int)$iTopicID;
		//sortering opvragen:
		$aProfiel=$this->_lid->getProfile();
		if(isset($aProfiel['forum_postsortering'])){
			if($aProfiel['forum_postsortering']=='DESC'){
				$sSorteerVolgorde='DESC';
			}else{
				$sSorteerVolgorde='ASC';
			}
		}else{
			$sSorteerVolgorde='ASC';
		}
		//ook op bevestiging wachtende berichten van niet ingelogde gebruikers zichtbaar maken
		//voor moderators
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			$zichtBaarClause="( topic.zichtbaar='zichtbaar' OR topic.zichtbaar='wacht_goedkeuring' )";
		}else{
			$zichtBaarClause="topic.zichtbaar='zichtbaar'";
		}
		//zoo, uberdeuberdeuber query om een topic op te halen. Namen worden
		//ook opgehaald in deze query, die worden door forumcontent weer 
		//doorgegeven aan getForumNaam();
		$sPostsQuery="
			SELECT
				categorie.titel AS categorieTitel,
				categorie.rechten_read AS rechten_read,
				categorie.rechten_post AS rechten_post,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				topic.zichtbaar AS zichtbaar,
				post.uid AS uid,
					lid.nickname AS nickname,
					lid.voornaam AS voornaam,
					lid.tussenvoegsel AS tussenvoegsel,
					lid.achternaam AS achternaam,
					lid.postfix AS postfix, 
					lid.geslacht AS geslacht, 
					lid.status AS status,
				post.id AS postID,
				post.tekst AS tekst,
				post.bbcode_uid AS bbcode_uid,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum
			FROM
				forum_topic topic
			INNER JOIN 
				forum_cat categorie ON (categorie.id=topic.categorie)
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			INNER JOIN 
				lid ON ( post.uid=lid.uid )
			WHERE
				topic.id=".$iTopicID."
			AND
				".$zichtBaarClause."
			ORDER BY
				post.datum ASC;";
		$rPostsResult=$this->_db->query($sPostsQuery);
		return $this->_db->result2array($rPostsResult); 
	}
	// een enkele post binnenhalen, bijvoorbeeld om te citeren/bewerken
	function getPost($iPostID){
		$iPostID=(int)$iPostID;
		$sPostQuery="
			SELECT
				categorie.id as categorieID,
				categorie.titel as categorieTitel,
				topic.id as topicID,
				topic.titel as topicTitel,
				topic.open as open,
				post.uid as uid, 
				post.tekst as tekst, 
				post.bbcode_uid as bbcode_uid, 
				post.datum as datum
			FROM
				forum_post post, 
				forum_topic topic,
				forum_cat categorie
			WHERE 
				post.id=".$iPostID."
			AND
				post.tid=topic.id
			AND
				topic.categorie=categorie.id
			AND
				topic.zichtbaar='zichtbaar'
			LIMIT 1;";
		$rPost=$this->_db->query($sPostQuery);
		if($this->_db->numRows($rPost)==1){
			$aPost=$this->_db->next($rPost);
			return $aPost;
		}else{	
			return false;
		}
	}	
	//laatste posts voor heel het forum.
	function getLastPosts($iAantal=false){
		if($iAantal===false){
			$iAantal=$this->_postsPerRss;
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
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				post.uid AS uid,
					lid.nickname AS nickname,
					lid.voornaam AS voornaam,
					lid.tussenvoegsel AS tussenvoegsel,
					lid.achternaam AS achternaam,
					lid.postfix AS postfix, 
					lid.geslacht AS geslacht, 
					lid.status AS status,
				post.id AS postID,
				post.tekst AS tekst,
				post.bbcode_uid AS bbcode_uid,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum
			FROM
				forum_topic topic
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			INNER JOIN 
				lid ON ( post.uid=lid.uid)
			WHERE
				topic.categorie != 4 AND
				topic.categorie != 6 AND
				topic.categorie != 7 AND
				topic.categorie != 8 AND
				topic.zichtbaar='zichtbaar'
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
	/*
	* -> post aan topic toevoegen
	* (als topic=0 opgegeven wordt een nieuwe gemaakt)
	* doordat het een universele functie is wordt de boel wat ingewikkeld, ook omdat er direct 
	* statistieken geupdate worden, wat anders waat 'duurder' zou worden door gebruik te maken 
	* van de speciaal daarvoor bestemde functies.
	*/
	function addPost($tekst, $bbcode_uid, $topic=0, $iCat=0, $titel='', $bModerated=false){
		//het is nu nog goed...
		$bError=false; 
		
		//ff wat gegevens netter maken...
		$topic=(int)$topic;
		$iCat=(int)$iCat;
		$uid=$this->_lid->getUid();
		$datumTijd=getDateTime();
		if(isset($_SERVER['REMOTE_ADDR'])){
			$ip=$_SERVER['REMOTE_ADDR'];
		}else{
			$ip='geen ip';
		}
		//nieuw topic of enkel een nieuwe post.
	 	if($topic==0){
	 		//zichtbaar of of wachtende op goedkeuring
	 		if($bModerated){ 
	 			$sZichtbaar='wacht_goedkeuring'; 
	 			$bUpdaten=false;
	 		}else{ 
	 			$sZichtbaar='zichtbaar';
	 			$bUpdaten=true;
	 		}
			//query maeken
	 		$sTopicQuery="
	 			INSERT INTO
		 			forum_topic
		 	 	(
			 	 	titel, categorie, uid, datumtijd, 
			 	 	lastuser, lastpost,  reacties, zichtbaar, open
			 	)VALUES(
			 		'".ucfirst($titel)."', ".$iCat.",	'".$uid."', '".$datumTijd."', 
			 		'".$uid."', '".$datumTijd."',	1, '".$sZichtbaar."', 1
			 	);";
			if($this->_db->query($sTopicQuery)){
				$topic=$this->_db->insert_id();
			 	$bTopicUpdaten=false;
			}else{
				//het gaet mis...
				$bError=true;
			}
		}else{
			//om later het topic nog ff up te daten
			$bTopicUpdaten=true;
			$bUpdaten=true;
		}
		if(!$bError){
			//nu nog de (eerste) posting invoegen
			$sPostQuery="
				INSERT INTO
					forum_post
				(
					tid, uid, tekst, bbcode_uid, datum, ip
				)VALUES(
					".$topic.",
					'".$uid."',
					'".ucfirst($tekst)."',
					'".$bbcode_uid."',
					'".$datumTijd."',
					'".$ip."'
				);";
			//deze query moet hier al uitgevoerd worden omdat anders het postid niet in de topicupdate query gerost kan worden.
			$bPostQuery=$this->_db->query($sPostQuery);
			$iPostID=$this->_db->insert_id();
			if($bUpdaten){
				if($bTopicUpdaten){
					//veldjes lastuser en lastpost updaten in forum_topic
					$sTopicUpdate="
						UPDATE
							forum_topic
						SET
							lastuser='".$uid."',
							lastpost='".$datumTijd."',
							lastpostID=".$iPostID.",
							reacties=reacties+1
						WHERE
							id=".$topic."
						LIMIT 1;";
					$this->_db->query($sTopicUpdate);
					//veldjes lastuser, lastpost, lastpostID en reacties updaten in forum_Cat
					$iCatID=$this->getCategorieVoorTopic($topic);
					$sCatUpdate="
						UPDATE
							forum_cat
						SET
							lastuser='".$uid."',
							lastpost='".$datumTijd."',
							lastpostID=".$iPostID.",
							lasttopic=".$topic.",
							reacties=reacties+1
						WHERE
							id=".$iCatID."
						LIMIT 1;"; 
					$this->_db->query($sCatUpdate);	
				}else{
					//veldjes voor laatste dingen updateren in forum_cat voor het nieuwe topic
					 $sCatUpdate="
						UPDATE
							forum_cat
						SET
							lastuser='".$uid."',
							lastpost='".$datumTijd."',
							lastpostID=".$iPostID.",
							lasttopic=".$topic.",
							reacties=reacties+1,
							topics=topics+1	
						WHERE
							id=".$iCat."
						LIMIT 1;"; 
					$this->_db->query($sCatUpdate);
				}
			}
			if($bPostQuery){
				return $topic;
			}else{
				return false;
			}
		}else{
			//hier is de topic query al misgegaan
			return false;
		}
	}
	//posts bewerken
	function editPost($iPostID, $sBericht, $bbcode_uid){
		$datumTijd=getDateTime();
		$sEditQuery="
			UPDATE
				forum_post
			SET
				tekst='".$sBericht."',
				bbcode_uid='".$bbcode_uid."',
				bewerkDatum='".$datumTijd."'
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		return $this->_db->query($sEditQuery);
	}
	//post verwijderen
	function deletePost($iPostID){
		$iPostID=(int)$iPostID;
		$iTopicID=$this->getTopicVoorPostID($iPostID);
		$sDeletePost="
			DELETE FROM
				forum_post
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		if($this->_db->query($sDeletePost)){
			return $this->updateTopicStats($iTopicID);
		}else{
			return false;
		}
	}
	//topic verwijderen
	function deleteTopic($iTopicID){
		$iTopicID=(int)$iTopicID;
		//dit moet vóór het verwijderen!
		$iCatID=$this->getCategorieVoorTopic($iTopicID);
		$aDelete[]="DELETE FROM	forum_post WHERE tid=".$iTopicID.";";
		$aDelete[]="DELETE FROM	forum_topic WHERE id=".$iTopicID." LIMIT 1;";
		//query's om polls weg te gooien, als er niets bestaat voor dit topicID dan 
		//wordt er dus ook niets weggegooid
		$aDelete[]="DELETE FROM	forum_poll_stemmen WHERE topicID=".$iTopicID.";";
		$aDelete[]="DELETE FROM forum_poll WHERE topicID=".$iTopicID.";";
		$bReturn=true;
		foreach($aDelete as $sDelete){
			if($this->_db->query($sDelete)===false) $bReturn=false;
		}
		return $bReturn AND $this->updateCatStats($iCatID);
	}
	function sluitTopic($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				open='0'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	function openTopic($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				open='1'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	function maakTopicPlakkerig($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				plakkerig='1'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	function unmaakTopicPlakkerig($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				plakkerig='0'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	function keurTopicGoed($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				zichtbaar='zichtbaar'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		//query uitvoeren en stats voor topic opnieuw berekenen
		return $this->_db->query($sTopicQuery) AND $this->updateTopicStats($iTopicID);
	}
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
	function getTopicVoorPostID($iPostID){
		$iPostID=(int)$iPostID;
		$sPostQuery="
			SELECT
				tid
			FROM
				forum_post
			WHERE 
				id=".$iPostID."
			LIMIT 1;";
		$rPost=$this->_db->query($sPostQuery);
		if($this->_db->numRows($rPost)==1){
			$aPost=$this->_db->next($rPost);
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
	function catExistsVoorUser($iCatID){
		$iCatID=(int)$iCatID;
		$sCatQuery="
			SELECT
				rechten_read
			FROM
				forum_cat
			WHERE 
				id=".$iCatID."
			LIMIT 1;";
		$rCat=$this->_db->query($sCatQuery);
		if($this->_db->numRows($rCat)==1){
			$aCat=$this->_db->next($rCat);
			return $this->_lid->hasPermission($aCat['rechten_read']);
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
	//controleer of gebruiker rechten heeft om te posten in een topic
	function magBerichtToevoegen($iTopicID, $iOpen=2, $rechten_post=false){
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			return true;
		}else{
			//ff ophaelen uit db of het topoc open danwel dicht is.
			if($iOpen==2)
				if($this->isOpen($iTopicID)){ $iOpen=1; }else{ $iOpen=0;}
			if(!is_string($rechten_post)){
				//rechten_post niet meegegeven, rechten ophaelen in de db...
				$rechten_post=$this->getRechten_post($this->getCategorieVoorTopic($iTopicID));
			}
			if($this->_lid->hasPermission($rechten_post) AND $iOpen==1){
				return true;
			}else{
				return false;
			}
		}
	}
	function isOpen($iTopicID){
		$iTopicID=(int)$iTopicID;
		$sTopicQuery="
			SELECT
				open
			FROM
				forum_topic
			WHERE 
				id=".$iTopicID."
			LIMIT 1;";
		$rTopic=$this->_db->query($sTopicQuery);
		if($this->_db->numRows($rTopic)==1){
			$aTopic=$this->_db->next($rTopic);
			if($aTopic['open']==1){ return true; }else{ return false; }
		}else{	
			return false;
		}
	}
/***************************************************************************************************
*	Vragen over posts
*
***************************************************************************************************/	
	//hier moet nog controle in of het topic open of dicht is.
	function magBewerken($iPostID, $iPostUid=false, $iOpen=2, $rechten_post=false){
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			return true;
		}else{
			//ff ophaelen uit db of het topoc open danwel dicht is.
			$iTopicID=$this->getTopicVoorPostID($iPostID);
			if($iOpen==2){
				if($this->isOpen($iTopicID)){ $iOpen=1; }else{ $iOpen=0;}
			}
			if($rechten_post===false){
				//rechten_post niet meegegeven, rechten ophaelen in de db...
				$rechten_post=$this->getRechten_post($this->getCategorieVoorTopic($iTopicID));
			}
			if($this->_lid->hasPermission($rechten_post) AND $iOpen==1){
				if($iPostUid!==false){
					//met iPostUid controleren, is sneller
					return $iPostUid==$this->_lid->getUid();
				}else{
					//ophaelen uit db..
					$aPost=$this->getPost($iPostID);
					return $aPost['open'] AND ($aPost['uid']==$this->_lid->getUid());
				}
			}else{
				return false;
			}
		}
	}

/***************************************************************************************************
*	Updaten van stats in categorie en topic
*
***************************************************************************************************/	
	//dingen updaten voor het topic 
	function updateTopicStats($iTopicID){
		$iCatID=$this->getCategorieVoorTopic($iTopicID);
		if($iCatID!==false){
			$sTopicStats="
				SELECT 
					id, uid, datum
				FROM
					forum_post
				WHERE 
					tid=".$iTopicID."
				ORDER BY
					datum DESC
				LIMIT 1;";
			$rTopicStats=$this->_db->query($sTopicStats);
			$aTopicStats=$this->_db->next($rTopicStats);
			$sTopicUpdate="
				UPDATE
					forum_topic
				SET
					lastpostID=".$aTopicStats['id'].",
					lastuser='".$aTopicStats['uid']."',
					lastpost='".$aTopicStats['datum']."',
					reacties=(
						SELECT 
							COUNT(*) AS aantal 
						FROM 
							forum_post 
						WHERE 
							tid=".$iTopicID." 
						LIMIT 1)
				WHERE
					id=".$iTopicID."
				LIMIT 1;";
			return $this->_db->query($sTopicUpdate) AND $this->updateCatStats($iCatID);
		}else{
			return false;
		}
	}
	
	//dingen updaten voor de categorie.
	function updateCatStats($iCatID){
		$sCatStats="
			SELECT 
				id, lastuser, lastpostID, lastpost
			FROM
				forum_topic
			WHERE 
				categorie=".$iCatID."
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
*	Namen ed.
*
***************************************************************************************************/	
	function getForumNaam($uid=false, $aOnderwerpPost=false ){
		$bError=false;
		//als er geen uid is opgegeven, dan die van de huidige gebruiker gebruiken.
		if($uid===false)
			$uid=$this->_lid->getUid();
		//controleer of het uid al eens opgezocht is en dus in de array staat.
		//zo hoeft voor elke naam maar één keer een query gedaan te worden.
		if(isset($this->_forumNaamCache[$uid])){
			return $this->_forumNaamCache[$uid];
		}else{
			if($aOnderwerpPost===false){
				//naam ophalen uit de db.
				$sNaamQuery="
					SELECT 
						nickname, voornaam, tussenvoegsel, achternaam, postfix, geslacht, status
					FROM
						lid
					WHERE 
						uid='".$uid."'
					LIMIT 1;";
				$rNaam=$this->_db->query($sNaamQuery);
				if($this->_db->numRows($rNaam)==1){
					$aNaam=$this->_db->next($rNaam);
				}else{
					$bError=true;
				}
			}else{
				$aNaam=$aOnderwerpPost;
			}
			if($bError){
				return false;
			}else{
				if($uid=='x999'){
					$sNaam='extern';
				}else{
					//naam klussen.
					$aProfiel=$this->_lid->getProfile();
					//als er in het profiel is aangegeven dat men nicknames wil zien.
					if(isset($aProfiel['forum_name']) AND $aProfiel['forum_name']=='nick' AND trim($aNaam['nickname'])!=''){
						$sNaam=$aNaam['nickname'];
					}else{
						if($aNaam['status']=='S_NOVIET'){
							$sNaam='noviet '.$aNaam['voornaam'];
						}elseif($aNaam['status']=='S_KRINGEL'){
							$sNaam='~ '.$aNaam['voornaam'];
						}else{
							if($aNaam['geslacht']=='v'){
								$sNaam='ama. ';
							}else{
								$sNaam='am. ';
							}
							if($aNaam['tussenvoegsel']!=''){
								$sNaam.=ucfirst($aNaam['tussenvoegsel']).' ';
							}
							$sNaam.=$aNaam['achternaam'];
							if($aNaam['postfix']!=''){
								$sNaam.=' '.$aNaam['postfix'];
							}
							if($aNaam['status']=='S_OUDLID'){
								$sNaam.=' (oudlid)';
							}
						}//einde status if
					}
				}
				//naam in cache rossen.
				$this->_forumNaamCache[$uid]=$sNaam;
				return $sNaam;
			}
		}
	}
	function getParseTime(){
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec)-$this->_parseStart;
	}
}//einde classe Forum


class Topic{
	#runtime vars
		//topicID
		var $_topicID;
		
		//array met posts in dit topic
		var $_aPosts;			//alle posts in dit topic
		var $_aPost;			//huidige post.
		
		//array met cache voor
		//eventuele foutmelding
		var $_sError;
		
	#instellingen
	#
	#dit zijn de standaard instellingen 
		//het aantal berichten per pagina bij het weergeven van een topic.
		//de standaard, het kan wellicht nog een keer in het profiel gerost worden.
		var $_postsPerPagina=15; 	//deze werkt dus nog helemaal niet.
		
		//forumnaam, kan zijn [ nick | civitas ]
		//nick geeft niksnamen ("jieter") weer, civitas geeft dingen als "am. Waamgeester" weer
		var $_forumNaam='civitas';
		
	#dataobjecten
		var $_db;
		var $_lid;
	
	function Topic(&$lid, &$db){
		$this->_lid =& $lid;
		$this->_db =& $db;
		//settings uit het profiel ophaelen.
		$this->_loadSettings();
	}
	
	/*
	* instellingen uit het profiel ophalen en in de vars van deze classe rossen
	*/
	function _loadSettings(){
		//dingen ophalen uit het profiel....
		$aForumInstellingen=$this->_lid->getForumInstelling();
		if(isset($aForumInstellingen['forum_naam'])){
			$this->_forumNaam=$aForumInstellingen['forum_naam'];
		}
		//if(isset($aProfiel['forum_posts_per_pagina'])){
		// $this->_postsPerPagina=$aProfiel['forum_posts_per_pagina'];
		//}
	}
	/*
	*	domme string-aan-elkaar-plak-functie voor de naam, als uid meegegeven wordt ook namen ophalen uit db.
	*/
	function getForumNaam($uid=false){
		if($uid===false){
			//huidige post gebruiken om naam op te haelen
			$aNaam=$this->_aPost;
		}else{
			//naam uit db haelen
			$sNaamQuery="
				SELECT 
					nickname, voornaam, tussenvoegsel, achternaam, postfix, geslacht, status
				FROM
					lid
				WHERE 
					uid='".$uid."'
				LIMIT 1;";
			$rNaam=$this->_db->query($sNaamQuery);
			if($this->_db->numRows($rNaam)==1){
				$aNaam=$this->_db->next($rNaam);
			}else{
				//als de query faalt, wat nepgegevens invoeren, dan faalt de rest iig niet...
				$aNaam=array(
					'nickname' => 'onbekend', 'voornaam' => '', 'achternaam' => 'onbekend', 
					'postfix' => '',  'geslacht' => 'm', 'status' => 'S_LID');
			} 
		}
		//als er in het profiel is aangegeven dat men nicknames wil zien.
		if($this->_forumNaam=='nick' AND trim($aNaam['nickname'])!=''){
			$sNaam=$aNaam['nickname'];
		}else{
		//als er in het profiel is aangeven dat men civitasnamen wil zien.
			if($aNaam['status']=='S_NOVIET'){
				$sNaam='noviet '.$aNaam['voornaam'];
			}elseif($aNaam['status']=='S_KRINGEL'){
				$sNaam='~ '.$aNaam['voornaam'];
			}else{
				if($aNaam['geslacht']=='v'){ $sNaam='ama. '; }else{ $sNaam='am. ';	}
				if($aNaam['tussenvoegsel']!='') $sNaam.=ucfirst($aNaam['tussenvoegsel']).' ';
				$sNaam.=$aNaam['achternaam'];
				if($aNaam['postfix']!='') $sNaam.=' '.$aNaam['postfix'];
				if($aNaam['status']=='S_OUDLID') $sNaam.=' (oudlid)';
			}//einde status if
		}
		return $sNaam;
	}
	function _formatDatum($datum){
		if($datum=='0000-00-00 00:00:00'){
			return '';
		}else{
			if(date('Y-m-d')==substr($datum, 0, 10)){
				return 'vandaag om '.date("G:i", strtotime($datum));
			}elseif(date('Y-m-').(date('d')-1)==substr($datum, 0, 10)){
				return 'gisteren om '.date("G:i", strtotime($datum));;
			}else{
				return ' '.date("j-n-Y \o\m G:i", strtotime($datum));
			}
		}
	}
	/*
	* topic ophaelen uit de database, en in de classevars rossen.
	*/
	function loadTopic($iTopicID){
		//als dit waar is gaat alles goed. Zijn er fouten wordt bReturn vals gemaakt.
		$bReturn=true;
		//topicID in de klasse rossen en er zeker van zijn dat het een integer is.
		$this->_topicID=(int)$iTopicID;
		//zoo, uberdeuberdeuber query om een topic op te halen. 
		$sTopicQuery="
			SELECT
				categorie.titel AS categorieTitel,
				categorie.rechten_read AS rechten_read,
				categorie.rechten_post AS rechten_post,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				post.uid AS uid,
					lid.nickname AS nickname,
					lid.voornaam AS voornaam,
					lid.tussenvoegsel AS tussenvoegsel,
					lid.achternaam AS achternaam,
					lid.postfix AS postfix, 
					lid.geslacht AS geslacht, 
					lid.status AS status,
				post.id AS postID,
				post.tekst AS tekst,
				post.bbcode_uid AS bbcode_uid,
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum
			FROM
				forum_topic topic
			INNER JOIN 
				forum_cat categorie ON (categorie.id=topic.categorie)
			LEFT JOIN
				forum_post post ON( topic.id=post.tid )
			INNER JOIN 
				lid ON ( post.uid=lid.uid )
			WHERE
				topic.id=".$this->_topicID."
			ORDER BY
				post.datum ASC;";
		$rTopicResult=$this->_db->query($sTopicQuery);
		if($rTopicResult===false){
			$this->_aPost=$bReturn=false;
			$this->_sError="Er is iets intern foutgegaan met het databeest.";
		}else{
			if($this->_db->numRows($rTopicResult)==0){
				$this->_aPost=$bReturn=false;
				$this->_sError="Dit onderwerp bestaat niet.";
			}else{
				//posts in $this->aPosts stoppen.
				while($aPost=$this->_db->next($rTopicResult)){
					$this->_aPosts[]=$aPost;
				}
				//eerste post als huidige post zetten.
				$this->_aPost=$this->_aPosts[0];
				//controleren of het topic wel mag worden weergegeven door deze gebruiker
				if(!$this->_lid->hasPermission($this->_aPost['rechten_read'])){
					$this->_aPosts=$bReturn=false;
					$this->_sError="Voor dit onderwerp heeft u geen toegangsrechten";
				}
			}
		}
		return $bReturn;
	}
	//Naar de volgende post springen
	function nextPost(){ 
		//controleer of _aPost wel een array is...
		if(is_array($this->_aPost)){
			//bericht door de ubb parser heentrekken
			$bericht=bbview($this->_aPost['tekst'], $this->_aPost['bbcode_uid']);
			//zo, alleen de relevante dingen voor een post doorgeven...
			$aPost=array( 
				'postID'=> $this->_aPost['postID'], 
				'uid' => $this->_aPost['uid'], 
				'naam' => mb_htmlentities($this->getForumNaam()),
				'bericht' => $bericht,
				'datum' => $this->_formatDatum($this->_aPost['datum']), 
				'bewerkDatum' => $this->_formatDatum($this->_aPost['bewerkDatum']) );
			//de volgende post laden in $this->_aPosts. Als het de laatste uit de array is zal next false teruggeven
			$this->_aPost=next($this->_aPosts);
			//array met gegevens voor deze post teruggeven.
			return $aPost;
		}else{
			//we zijn bij het laatste bericht aangeland waarschijnlijk.
			return false;
		}
	}
	/*
	* Functie geeft een array met resultaten terug. Dan klopt er bijzonder weinig meer van
	* alle functies die per post zijn. dat zij zo. Enkel voor debugging dus dit.
	*/
	function getPosts(){

		while($aBericht=$this->nextPost()){
			$aBerichten[]=$aBericht;
		}
		return $aBerichten;
	}
	//categorie dingen
	function getCategorieID(){ return $this->_aPost['categorie'];}
	function getCategorieTitel(){ return $this->_aPost['categorieTitel']; }
	function getReadRechten(){ return $this->_aPost['rechten_read']; }
	function getPostRechten(){ return $this->_aPost['rechten_post']; }
	
	//topic dingen
	function getAantalPosts(){ return count($this->_aPosts); }
	function getID(){ return $this->_topicID; }
	function getTitel(){ return $this->_aPost['titel']; }
	function getSoort(){ return $this->_aPost['soort']; } // [ poll | lezing | standaard ]
	function getStarter(){ return $this->_aPost['startUID']; }
	function isOpen(){ return $this->_aPost['open']==1; }
	function isPlakkerig(){ return $this->_aPost['plakkerig']==0; }
	function magPosten(){
		return $this->_lid->hasPermission('P_FORUM_MOD') OR 
		( $this->_lid->hasPermission($this->getPostRechten()) AND $this->isOpen());
	}
	function magModereren(){ return $this->_lid->hasPermission('P_FORUM_MOD'); }
	
	//post dingen
	function getPostUid(){ return $this->_aPost['uid']; }
	function magBewerken(){
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			return true;
		}else{	
			if($this->magPosten()){
				return $this->getPostUid()==$this->_lid->getUid();
			}else{
				return false;
			}
		}
	}
	function magCiteren(){ return $this->magPosten(); }
	function magVerwijderen(){ return $this->_lid->hasPermission('P_FORUM_MOD'); }
	
	//eventuele error tijdens het uitvoeren van de classe opvragen.	
	function getError(){ return $this->_sError; }
}
?>
