<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forum.php
# -------------------------------------------------------------------
# Forum databaseklasse
# -------------------------------------------------------------------

require_once('class.forum.php');

class ForumOnderwerp extends Forum {
	
	//het onderwerp wat het huidige is.
	private $iTopicID=0;
	//eigenschappen van het onderwerp.
	private $aTopicProps=false;
	//posts in het onderwerp.
	private $aPosts=false;
	
	function ForumOnderwerp(){
		parent::Forum();
	}
	
	//een onderwerp laden aan de hand van een zich in dat onderwerp bevindende post.
	function loadByPostID($iPostID){
		$iTopicID=$this->getTopicVoorPostID((int)$iPostID);
		return $this->load($iTopicID); 
	}
	
	//een onderwerp laden aan de hand van een ID.
	//geeft true terug als het onderwerp succesvol geladen is en als het ingelogde lid
	//het onderwerp mag bekijken.
	function load($iTopicID){
		$this->iTopicID=(int)$iTopicID;
		$sTopicQuery="
			SELECT
				categorie.id AS categorieID,
				categorie.titel AS categorieTitel,
				categorie.rechten_read AS rechten_read,
				categorie.rechten_post AS rechten_post,
				topic.id AS topicID,
				topic.titel AS titel,
				topic.uid AS startUID,
				topic.categorie AS categorie,
				topic.open AS open,
				topic.plakkerig AS plakkerig,
				topic.soort AS soort,
				topic.zichtbaar AS topicZichtbaar
			FROM 
				forum_topic topic
			INNER JOIN 
				forum_cat categorie ON (categorie.id=topic.categorie)
			WHERE
				topic.id=".$this->getID()."
			AND
				( topic.zichtbaar='zichtbaar' OR topic.zichtbaar='wacht_goedkeuring' )
			LIMIT 1;";
		$rTopic=$this->_db->query($sTopicQuery);
		$this->aTopicProps=$this->_db->next($rTopic);
		//Mag de gebruiker het huidige onderwerp bekijken, of is de gebruiker een FORUM_MOD
		if($this->_lid->hasPermission($this->aTopicProps['rechten_read']) OR $this->isModerator()){
			//onderwerp mag worden gelezen, dan ook de posts ervoor inladen.
			return $this->loadPosts();
		}else{
			//helaas, dit topic mag niet worden gelezen, geen posts laden, meteen
			//false teruggeven en géén posts inladen. 
			return false;
		}
	}
	//posts inladen voor het huidige onderwerp. Kan enkel intern aangeroepen worden.
	//geeft true terug als er een array uit komt.
	private function loadPosts(){
		$zichtBaarClause="post.zichtbaar='zichtbaar'";
		if($this->isModerator()){ 
			$zichtBaarClause.=" OR post.zichtbaar='wacht_goedkeuring'";
		}
		$sPostsQuery="
			SELECT
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
				post.datum AS datum,
				post.bewerkDatum AS bewerkDatum,
				post.zichtbaar AS zichtbaar,
				post.ip AS ip
			FROM
				forum_post post
			INNER JOIN 
				lid ON ( post.uid=lid.uid )
			WHERE
				post.tid=".$this->getID()."
			AND
				( ".$zichtBaarClause." ) 
			ORDER BY
				post.datum ASC;";
		$rPostsResult=$this->_db->query($sPostsQuery);
		$this->aPosts=$this->_db->result2array($rPostsResult);
		return is_array($this->aPosts);
	}	
	
	//als de categorie handmatig moet worden ingesteld 
	//(bij het toevoegen van een nieuw onderwerp bijvoorbeeld)
	public function setCat($iCatID){
		$iCatID=(int)$iCatID;
		$this->aTopicProps=array(
			'categorieID' => $iCatID,
			'categorieTitel' => $this->getCategorieTitel($iCatID),
			'rechten_post' => $this->getRechten_post($iCatID),
			'topicZichtbaar' => 'zichtbaar');
	}	
	
	//categorie
	public function getCatID(){ return $this->aTopicProps['categorieID']; }
	public function getCatTitel(){ return $this->aTopicProps['categorieTitel']; }
	public function getRechtenPost(){ return $this->aTopicProps['rechten_post']; }
	public function magPosten(){ return $this->_lid->hasPermission($this->getRechtenPost()); }
	
	//topic
	public function getID(){ return $this->iTopicID; }
	public function getTitel(){ return $this->aTopicProps['titel']; }
	public function getZichtbaarheid(){ return $this->aTopicProps['topicZichtbaar']; }
	public function setZichtbaarheid($zichtbaarheid){ 
		$this->aTopicProps['topicZichtbaar']=$zichtbaarheid;
	}
	public function isOpen(){ return $this->aTopicProps['open']==1; }
	public function isPlakkerig(){ return $this->aTopicProps['plakkerig']==1; }
	public function needsModeration(){ return !$this->isIngelogged(); }
	public function getSoort(){ return $this->aTopicProps['soort']; }
	public function getSize(){ return count($this->aPosts); }
	
	public function magCiteren(){ return $this->magToevoegen(); }
	public function magToevoegen(){
		//FORUM_MOD mag alles bewerken
		if($this->isModerator()){ return true;}
		return $this->magPosten() AND $this->isOpen();
	}
	
	public function magBewerken($iPostID){
		//FORUM_MOD mag alles bewerken
		if($this->isModerator()){ return true;}
		//niet ingeloggede mensen mogen nooit bewerken.
		if($this->_lid->getUid()=='x999'){ return false;}
		
		//intern, nu nog of de huidige post mag.
		if($this->magPosten() AND $this->isOpen()){
			//nu alleen nog controleren of het bericht van de huidige gebruiker is.
			$aPost=$this->getSinglePost($iPostID);
			return $aPost['uid']==$this->_lid->getUid();
		}else{
			//geen rechten om te posten, en niet open.
			return false;
		}
	}
	
	//geeft een array met posts terug als die tenminste zijn ingeladen.
	//Uitvoer van deze functie wordt gebruikt als afweging voor het wel 
	//weergeven van het onderwerp dan wel een foutmelding.
	function getPosts(){
		if(is_array($this->aPosts) AND is_array($this->aTopicProps)){
			return $this->aPosts;
		}else{
			return false;
		}
	}
	// een enkele post binnenhalen, bijvoorbeeld om te citeren/bewerken
	function getSinglePost($iPostID){
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
			LIMIT 1;";
		$rPost=$this->_db->query($sPostQuery);
		if($this->_db->numRows($rPost)==1){
			return $this->_db->next($rPost);
		}else{	
			return false;
		}
	}	
	
	//voeg een onderwerp toe met een titel.
	//Indien succesvol komt het zojuist ingevoerde onderwerp-id eruit, anders
	//false.
	function addTopic($titel){
		$titel=$this->_db->escape(ucfirst($titel));
		if($this->needsModeration()){ 
			$this->setZichtbaarheid('wacht_goedkeuring'); 
		}
		$sTopicQuery="
 			INSERT INTO
	 			forum_topic
	 	 	(
		 	 	titel, categorie, uid, datumtijd, 
		 	 	lastuser, lastpost,  reacties, zichtbaar, open
		 	)VALUES(
		 		'".$titel."', ".$this->getCatID().", '".$this->_lid->getUid()."', '".getDateTime()."', 
		 		'".$this->_lid->getUid()."', '".getDateTime()."',	0, '".$this->getZichtbaarheid()."', 1
		 	);";

		if($this->_db->query($sTopicQuery)){
			//het zojuist ingevoerde onderwerp inladen...
			$this->load($this->_db->insert_id());
			//en hertellen
			$this->recountTopic();
			return $this->getID();
		}else{
			return false;
		}
	}
	
	//Een post toevoegen aan het huidige onderwerp.
	//Indien succesvol: nieuwe post-id komt terug. Anders false.
	function addPost($tekst){
		$tekst=$this->_db->escape(trim($tekst));
		if($this->iTopicID==0){ die('ForumOnderwerp::addPost() geen onderwerp ingeladen'); }
		//het ip-adres bepalen van de post.
		if(isset($_SERVER['REMOTE_ADDR'])){ $ip=$_SERVER['REMOTE_ADDR']; }else{ $ip='0.0.0.0'; }
 	
 		//kijken of een moderatiestap nodig is...
 		if($this->needsModeration()){
 			$zichtbaarheid='wacht_goedkeuring';
 		}else{
 			//overerving van het onderwerp
 			$zichtbaarheid=$this->getZichtbaarheid();
 		}
		$sPostQuery="
			INSERT INTO
				forum_post
			(
				tid, uid, tekst, datum, ip, zichtbaar
			)VALUES(
				".$this->getID().",
				'".$this->_lid->getUid()."',
				'".ucfirst($tekst)."',
				'".getDateTime()."',
				'".$ip."',
				'".$zichtbaarheid."'
			);";
		
		if($this->_db->query($sPostQuery)){
			//een mailtje sturen naar de pubcie om de boel bevestigd te krijgen
			if($this->needsModeration()){
				//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
	 			mail('pubcie@csrdelft.nl', 'Nieuw bericht in extern wacht op goedkeuring', 
	 				"yo, er is een nieuw bericht in extern, wat op goedkeuring wacht \r\n".
	 			 	"http://csrdelft.nl/forum/onderwerp/".$this->getID()."\r\n".
	 			 	"\r\nDe inhoud van het bericht is als volgt: \r\n\r\n".$tekst."\r\n\r\nEINDE BERICHT");
	 		}
	 		//de boel hertellen:
	 		$this->recountTopic();
	 		return $this->_db->insert_id();
		}else{
			return false;
		}	
	}
	//posts bewerken
	function editPost($iPostID, $sBericht){
		$sEditQuery="
			UPDATE
				forum_post
			SET
				tekst='".$sBericht."',
				bewerkDatum='".getDateTime()."'
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		return $this->_db->query($sEditQuery);
	}
	
	//post verwijderen.
	//posts worden nooit echt verwijderd via de forumsoftware.
	function deletePost($iPostID){
		$iPostID=(int)$iPostID;
		//hele onderwerp wegkekken als er maar één bericht is.
		if($this->getSize()==1){
			return $this->deleteTopic();
		}
		$sDeletePost="
			UPDATE
				forum_post
			SET 
				zichtbaar='verwijderd'
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		if($this->_db->query($sDeletePost)){
			return $this->recountTopic();
		}else{
			return false;
		}
	}
	
	//een onderwerp verwijderen.
	function deleteTopic(){

		$aDelete[]="DELETE FROM	forum_post WHERE tid=".$this->getID().";";
		$aDelete[]="DELETE FROM	forum_topic WHERE id=".$this->getID()." LIMIT 1;";
		//query's om polls weg te gooien, als er niets bestaat voor dit topicID dan 
		//wordt er dus ook niets weggegooid
		$aDelete[]="DELETE FROM	forum_poll_stemmen WHERE topicID=".$this->getID().";";
		$aDelete[]="DELETE FROM forum_poll WHERE topicID=".$this->getID().";";
		$bReturn=true;
		foreach($aDelete as $sDelete){
			if($this->_db->query($sDelete)===false) $bReturn=false;
		}
		return $bReturn AND $this->updateCatStats($this->getCatID());
	}
	
	function toggleOpenheid(){
		if($this->aTopicProps['open']=='0'){
			$status='1';
		}else{
			$status='0';
		}
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				open='".$status."'
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	
	function togglePlakkerigheid(){
		if($this->aTopicProps['plakkerig']=='0'){
			$status='1';
		}else{
			$status='0';
		}
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				plakkerig='".$status."'
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		return $this->_db->query($sTopicQuery);
	}
	
	function keurGoed($iPostID){
		$iPostID=(int)$iPostID;
		$sPostQuery="
			UPDATE
				forum_post
			SET
				zichtbaar='zichtbaar'
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		$iTopicID=$this->getTopicVoorPostID($iPostID);
		$sTopicQuery="
			UPDATE 
				forum_topic
			SET
				zichtbaar='zichtbaar'
			WHERE
				id=".$iTopicID."
			LIMIT 1;";
		//queries uitvoeren en stats voor topic opnieuw berekenen
		return $this->_db->query($sPostQuery) AND $this->_db->query($sTopicQuery) AND $this->recountTopic();
	}
	
	//dingen updaten voor het huidige topic 
	function recountTopic(){
		$sTopicStats="
			SELECT 
				id, uid, datum
			FROM
				forum_post
			WHERE 
				tid=".$this->getID()." AND
				zichtbaar='zichtbaar'
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
						tid=".$this->getID()." 
					LIMIT 1)
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		return $this->_db->query($sTopicUpdate) AND $this->updateCatStats($this->getCatID());
	}
}
