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
	
	//aantal zoekresultaten
	var $_aantalZoekResultaten=20;
	
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
	function getPostsVoorRss($iAantal=false){
		if($iAantal===false){
			$iAantal=$this->_postsPerRss;
		}
		//uitmaken welke categorieën er in de rss feed komen.
		$sCategorieClause='topic.categorie=2 OR topic.categorie=3 ';
		//geen zandbak: $sCagegorieClause.='OR topic.categorie=4 ';
		if($this->_lid->hasPermission('P_LEDEN_READ')){ $sCategorieClause.='OR topic.categorie=1 '; }
		if($this->_lid->hasPermission('P_OUDLEDEN_READ')){ $sCategorieClause.='OR topic.categorie=8 '; }
		if($this->_lid->hasPermission('P_FORUM_MOD')){ $sCategorieClause.='OR topic.categorie=6 '; }
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
				topic.zichtbaar='zichtbaar' AND
				( ".$sCategorieClause." )
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
				if($bModerated){
					//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
	 				mail('pubcie@csrdelft.nl', 'Nieuw bericht in extern wacht op goedkeuring', "yo, er is een nieuw bericht in extern, wat op".
	 				 "goedkeuring wacht \r\nhttp://csrdelft.nl/forum/onderwerp/".$topic);
	 			}
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
	//controleer of gebruiker rechten heeft om te posten in een topic
	function magBerichtToevoegen($iTopicID, $iOpen=2, $rechten_post=false){
		//mods mogen sowiso posten.
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			return true;
		}else{
			//als $iOpen==2 is er geen waarde meegegeven met de functieaanroep, het moet nog uit de db komen.
			if($iOpen==2){ if($this->isOpen($iTopicID)){ $iOpen=1; }else{ $iOpen=0; } }
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
	function magBewerken($iPostID, $iPostUid=false, $iOpen=2, $rechten_post=false){
		//FORUM_MOD mag alles bewerken
		if($this->_lid->hasPermission('P_FORUM_MOD')){
			return true;
		}else{
			//uitzoeken of niet mods mogen bewerken.
			$iTopicID=$this->getTopicVoorPostID($iPostID);
			//Kijken of het topic open of dicht is. Bewerken mag alleen in open topics. 
			//Als $iOpen==2 dan is er niets meegegeven met de functieaanroep, en moet het uit de database komen
			if($iOpen==2){
				if($this->isOpen($iTopicID)){ $iOpen=1; }else{ $iOpen=0;}
			}
			//als $rechten_post===false dan is er geen string met rechten voor het posten meegegeven met de 
			//functieaanroep, ophalen uit de database dan maar.
			if($rechten_post===false){ $rechten_post=$this->getRechten_post($this->getCategorieVoorTopic($iTopicID));	}
			if($this->_lid->hasPermission($rechten_post) AND $iOpen==1){
				//nu alleen nog controleren of het bericht van de huidige gebruiker is.
				//als $iPostUid!==false dan is er geen uid van de post meegegeven, ophalen uit de db dan maar...
				if($iPostUid!==false){
					//extern mag uberhaupt niet bewerken.
					if($iPostUid!='x999'){
						return $iPostUid==$this->_lid->getUid();
					}else{
						return false;
					}
				}else{
					$aPost=$this->getPost($iPostID);
					//extern mag uberhaupt niet bewerken.
					if($aPost['uid']!='x999'){
						return $aPost['open'] AND ($aPost['uid']==$this->_lid->getUid());
					}else{
						return false;
					}
				}
			}else{
				//geen rechten om te posten, en niet open.
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
				//naam ophalen uit de db, als er geen array meegegeven is waarin de gegevens al staan.
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
				//array die met de functieaanroep is meegegeven gebruiken, scheelt een query
				$aNaam=$aOnderwerpPost;
			}
			if(!$bError){
				//naam klussen.
				$aProfiel=$this->_lid->getProfile();
				//als er in het profiel is aangegeven dat men nicknames wil zien.
				if(isset($aProfiel['forum_name']) AND $aProfiel['forum_name']=='nick' AND trim($aNaam['nickname'])!=''){
					$sNaam=$aNaam['nickname'];
				}else{
					//kijken wat voor soort lid dit is.
					if($aNaam['status']=='S_NOVIET'){
						$sNaam='noviet '.$aNaam['voornaam'];
					}elseif($aNaam['status']=='S_KRINGEL'){
						$sNaam='~ '.$aNaam['voornaam'];
					}elseif($aNaam['status']=='S_NOBODY'){
						$sNaam='extern'; //voor 'anonieme' posts in de categorie extern.
					}else{
						if($aNaam['geslacht']=='v'){ $sNaam='ama. '; }else{ $sNaam='am. '; }
						if($aNaam['tussenvoegsel']!=''){ $sNaam.=ucfirst($aNaam['tussenvoegsel']).' '; }
						$sNaam.=$aNaam['achternaam'];
						if($aNaam['postfix']!=''){ $sNaam.=' '.$aNaam['postfix']; }
						if($aNaam['status']=='S_OUDLID'){ $sNaam.=' (oudlid)'; }
					}//einde status if
				}//einde nickname vs civitasnaam if
				//naam in cache rossen.
				$this->_forumNaamCache[$uid]=$sNaam;
				return $sNaam;
			//er is een fout opgetreden...
			}else{ return 'FOUT'; }
		}
	}
	function searchPosts($sZoekQuery){
		if(preg_match('/^[a-zA-Z0-9 \-\+\']*$/', $sZoekQuery)){
			//bekijken waarin gezocht mag worden...
			$sCategorieClause='topic.categorie=2 OR topic.categorie=3 ';
			if($this->_lid->hasPermission('P_LEDEN_READ')){ $sCategorieClause.='OR topic.categorie=1 '; }
			if($this->_lid->hasPermission('P_FORUM_READ')){ $sCategorieClause.=' OR topic.categorie=4 '; }
			if($this->_lid->hasPermission('P_OUDLEDEN_READ')){ $sCategorieClause.='OR topic.categorie=8 '; }
			if($this->_lid->hasPermission('P_FORUM_MOD')){ $sCategorieClause.='OR topic.categorie=6 '; }
			//sZoekQuery controleren:
			$sZoekQuery=$this->_db->escape(trim($sZoekQuery));
			
			//zoo, uberdeuberdeuber query om een topic op te halen. Namen worden
			//ook opgehaald in deze query, die worden door forumcontent weer 
			//doorgegeven aan getForumNaam();
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
					post.bewerkDatum AS bewerkDatum,
					count(*) AS aantal
				FROM
					forum_post post
				INNER JOIN
					forum_topic topic ON( post.tid=topic.id )
				INNER JOIN
					forum_cat cat ON( topic.categorie=cat.id )
				INNER JOIN 
					lid ON ( post.uid=lid.uid)
				WHERE
					topic.zichtbaar='zichtbaar' AND
					( ".$sCategorieClause." ) AND
					MATCH(post.tekst, topic.titel )AGAINST( '".$sZoekQuery."' IN BOOLEAN MODE )
				GROUP BY
					topic.id
				ORDER BY
					post.datum DESC
				LIMIT
					".$this->_aantalZoekResultaten.";";
				$rSearchResult=$this->_db->query($sSearchQuery);
				return $this->_db->result2array($rSearchResult);
		}else{
			return false;
		}
	}
	function getParseTime(){
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec)-$this->_parseStart;
	}
}//einde classe Forum
?>
