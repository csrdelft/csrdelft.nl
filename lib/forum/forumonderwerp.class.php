<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.forumonderwerp.php
# -------------------------------------------------------------------
# Forum databaseklasse
# -------------------------------------------------------------------

require_once 'forum.class.php';
require_once 'forumcategorie.class.php';

class ForumOnderwerp{

	private $ID=0;

	//object van de categorie waar het huidige onderwerp in zit.
	private $categorie;

	private $titel;
	private $uid;
	private $zichtbaar='zichtbaar';
	private $open=1;
	private $plakkerig=0;
	private $reacties=0;
	private $lastuser;
	private $lastpost;
	private $lastpostID;

	private $pagina=1;
	private $paginaCount=null;

	protected $posts=null;

	private $error;

	function __construct($init, $pagina=1){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$init=(int)$init;
			if($init!=0){
				$this->pagina=(int)$pagina;
				$this->load($init);
			}
			//we laden alleen als topicid!=0, bij 0 willen we een nieuw onderwerp maken,
			//dus hoeven we nog niets uit de db te halen.
		}
	}

	//een onderwerp laden aan de hand van een zich in dat onderwerp bevindende post.
	public static function loadByPostID($iPostID, $loadChildren=true){
		$iTopicInfo=Forum::getTopicVoorPostID((int)$iPostID);
		return new ForumOnderwerp($iTopicInfo['tid'], $loadChildren);
	}

	//redirected naar een onderwerp aan de hand van een zich in dat onderwerp bevindende post.
	public static function redirectByPostID($iPostID){
		$iTopicInfo=Forum::getTopicVoorPostID((int)$iPostID);
		header('location: '.CSR_ROOT.'communicatie/forum/onderwerp/'.$iTopicInfo['tid'].'/'.$iTopicInfo['pagina'].'#post'.$iPostID);
		exit;
	}

	/*
	 * stop de dingen uit een array in de object-eigenschappen.
	 * Dit wordt gebruikt door __construct() en load(), vandaar in een losse methode;
	 */
	private function array2properties($onderwerp){
		$this->ID=$onderwerp['id'];
		$this->titel=$onderwerp['titel'];
		$this->uid=$onderwerp['uid'];
		$this->setCategorie($onderwerp['categorie']);
		$this->open=$onderwerp['open'];
		$this->plakkerig=$onderwerp['plakkerig'];
		$this->zichtbaar=$onderwerp['zichtbaar'];
		$this->reacties=$onderwerp['reacties'];
		$this->lastpost=$onderwerp['lastpost'];
		$this->lastpostID=$onderwerp['lastpostID'];
		$this->lastuser=$onderwerp['lastuser'];
	}

	//een onderwerp laden aan de hand van een ID.
	//geeft true terug als het onderwerp succesvol geladen is en als het ingelogde lid
	//het onderwerp mag bekijken.
	function load($topicID){
		$this->ID=(int)$topicID;
		$db=MySql::instance();

		$sTopicQuery="
			SELECT
				id, titel, uid, categorie, open, plakkerig, zichtbaar,
				lastpost, lastuser, lastpostID, reacties
			FROM
				forum_topic topic
			WHERE
				topic.id=".$this->getID()."
			LIMIT 1;";
		$onderwerp=$db->getRow($sTopicQuery);
		if(!is_array($onderwerp)){
			$this->error='Dit onderwerp bestaat niet. (ForumOnderwerp::load())';
			return false;
		}

		$this->array2properties($onderwerp);

		if(!$this->magBekijken()){
			//helaas, dit topic mag niet worden gelezen, geen posts laden, meteen
			//false teruggeven en géén posts inladen.
			$this->error='Gebruiker mag dit onderwerp niet bekijken. (ForumOnderwerp::load())';
			return false;
		}
		$this->loadPosts();
	}

	//posts inladen voor het huidige onderwerp. Kan enkel intern aangeroepen worden.
	//geeft true terug als er berichten zijn ingeladen.
	private function loadPosts($postOffset=null){
		$zichtBaarClause="post.zichtbaar='zichtbaar'";
		if(Forum::isModerator()){
			$zichtBaarClause.=" OR post.zichtbaar='wacht_goedkeuring' OR post.zichtbaar='spam'";
		}
		if($postOffset===null){
			$postOffset=($this->pagina-1)*Forum::getPostsPerPagina();
		}
		$sPostsQuery="
			SELECT
				uid, id, tekst, datum, bewerkDatum, bewerkt, zichtbaar, ip
			FROM
				forum_post post
			WHERE
				post.tid=".$this->getID()."
			AND
				( ".$zichtBaarClause." )
			ORDER BY
				post.datum ASC
			LIMIT
				".$postOffset.", ".Forum::getPostsPerPagina().";";
		$this->posts=MySql::instance()->query2array($sPostsQuery);

		if(!is_array($this->posts)){
			//er is wellicht een niet bestaande pagina opgevraagd, hoogst mogelijke pagina terug....
			if($postOffset>0){
				return $this->loadPosts(($this->getPaginaCount()-1)*Forum::getPostsPerPagina());
			}
			$this->error='Er konden geen berichten worden ingeladen. (ForumOnderwerp::loadPosts())';
		}
		return is_array($this->posts);
	}

	public function filter2008(){
		foreach($this->posts as $key => $post){
			if(LidCache::getLid($post['uid'])->getLichting()=='2008'){
				$this->posts[$key]['filtered']=true;
			}
		}
	}
	//als de categorie handmatig moet worden ingesteld
	//(bij het toevoegen van een nieuw onderwerp bijvoorbeeld)
	public function setCategorie($iCatID){
		$this->categorie=(int)$iCatID;
	}

	//categorie
	public function getCategorieID(){ return $this->getCategorie()->getID(); }
	public function getCategorie($force=false){
		if($force OR !($this->categorie instanceof ForumCategorie)){
			$this->categorie=new ForumCategorie($this->categorie, 1);
		}
		return $this->categorie;
	}
	public function getRechtenPost(){ return $this->getCategorie()->getRechten_post(); }
	public function magPosten(){
		return LoginLid::instance()->hasPermission($this->getRechtenPost());
	}

	//topic
	public function getID(){ return $this->ID; }
	public function getTitel(){ return $this->titel; }
	public function getUid(){ return $this->uid; }
	public function getZichtbaarheid(){ return $this->zichtbaar; }
	public function setZichtbaarheid($zichtbaarheid){ $this->zichtbaar=$zichtbaarheid; }
	public function isOpen(){ return $this->open==1; }
	public function isPlakkerig(){ return $this->plakkerig==1; }
	public function needsModeration(){ return !Forum::isIngelogged(); }
	public function getReacties(){ return $this->reacties; }
	public function getLastpost(){ return $this->lastpost; }
	public function getLastpostID(){ return $this->lastpostID; }
	public function getLastuser(){ return $this->lastuser; }

	public function getPagina(){ return $this->pagina; }

	function getPaginaCount($force=false){
		if($this->paginaCount===null){
			$db=MySql::instance();

			$zichtBaarClause="post.zichtbaar='zichtbaar'";
			if(Forum::isModerator()){
				$zichtBaarClause.=" OR post.zichtbaar='wacht_goedkeuring' OR post.zichtbaar='spam'";
			}
			$sTopicQuery="
				SELECT count(*) as aantal
				FROM forum_post as post
				WHERE tid=".$this->getID()."
				AND ( ".$zichtBaarClause." )
				LIMIT 1;";
			$topic=$db->getRow($sTopicQuery);
			if(is_array($topic)){
				$aantal=ceil($topic['aantal']/Forum::getPostsPerPagina());
				if($aantal>0){
					$this->paginaCount=$aantal;
				}
			}else{
				$this->paginaCount=1;
			}
		}
		return $this->paginaCount;
	}

	public function getSize(){
		if($this->posts===null){
			$this->loadPosts();
		}
		return count($this->posts);
	}

	public function magBekijken(){
		if(Forum::isModerator()){ return true; }
		if(!($this->getCategorie() instanceof ForumCategorie)){
			throw new Exception('ForumOnderwerp::magBekijken(): Geen onderwerp ingeladen.');
		}else{
			return LoginLid::instance()->hasPermission($this->getCategorie()->getRechten_read());
		}
	}
	public function isIngelogged(){ return Forum::isIngelogged(); }
	public function isModerator(){ return Forum::isModerator(); }
	public function magCiteren(){ return $this->magToevoegen(); }
	public function magToevoegen(){
		//if(Forum::isModerator()){ return true; }
		return $this->magPosten() AND $this->isOpen();
	}

	public function magBewerken($iPostID){
		$uid=LoginLid::instance()->getUid();

		if(Forum::isModerator()){ return true;}
		if($uid=='x999'){ return false;}

		//intern, nu nog of de huidige post mag.
		if($this->magPosten() AND $this->isOpen()){
			//nu alleen nog controleren of het bericht van de huidige gebruiker is.
			$aPost=$this->getSinglePost($iPostID);
			return $aPost['uid']==$uid;
		}else{
			//geen rechten om te posten, en niet open.
			return false;
		}
	}

	//geeft een array met posts terug als die tenminste zijn ingeladen.
	//Uitvoer van deze functie wordt gebruikt als afweging voor het wel
	//weergeven van het onderwerp dan wel een foutmelding.
	function getPosts($forcereload=false){
		if($this->posts===null OR $forcereload){
			if($this->magBekijken()){
				$this->loadPosts();
			}else{
				//gebruiker mag posts niet bekijken, dus we moten false teruggeven.
				$this->posts=false;
			}
		}

		if(is_array($this->posts)){
			//nobold for berr vanaf nu
			$return=array();
			foreach($this->posts as $post){
				if($post['uid']=='0308' AND $post['datum']>'2010-05-19 13:30:00'){
					$post['tekst']='[nobold]'.$post['tekst'].'[/nobold]';
				}
				$return[]=$post;
			}

			return $return;
		}else{
			return false;
		}
	}
	// een enkele post binnenhalen, bijvoorbeeld om te citeren/bewerken
	public static function getSinglePost($iPostID){
		$iPostID=(int)$iPostID;

		$db=MySql::instance();
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
		return $db->getRow($sPostQuery);
	}

	//voeg een onderwerp toe met een titel.
	//Indien succesvol komt het zojuist ingevoerde onderwerp-id eruit, anders
	//false.
	function add($titel){
		$db=MySql::instance();
		$uid=LoginLid::instance()->getUid();

		$titel=$db->escape(ucfirst($titel));
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
		 		'".$titel."', ".$this->getCategorieID().", '".$uid."', '".getDateTime()."',
		 		'".$uid."', '".getDateTime()."',	0, '".$this->getZichtbaarheid()."', 1
		 	);";

		if($db->query($sTopicQuery)){
			//het zojuist ingevoerde onderwerp inladen...
			$this->load($db->insert_id());
			//en hertellen
			$this->recount();
			return $this->getID();
		}else{
			return false;
		}
	}

	//Een post toevoegen aan het huidige onderwerp.
	//Indien succesvol: nieuwe post-id komt terug. Anders false.
	public function addPost($tekst){
		$db=MySql::instance();

		$tekst=$db->escape(trim($tekst));
		if(!($this->getCategorie() instanceof ForumCategorie)){
			die('ForumOnderwerp::addPost() geen onderwerp ingeladen.');
		}

		//het ip-adres bepalen van de post.
		if(isset($_SERVER['REMOTE_ADDR'])){ $ip=$_SERVER['REMOTE_ADDR']; }else{ $ip='0.0.0.0'; }

		require_once 'simplespamfilter.class.php';
		$filter=new SimpleSpamfilter($tekst);

 		//kijken of een moderatiestap nodig is...
 		if($this->needsModeration()){
 			if($filter->isSpam()){
				$zichtbaarheid='spam';
 			}else{
 				$zichtbaarheid='wacht_goedkeuring';
 			}
 		}else{
 			//Als de moderatiestap niet nodig is, dan erft een post de zichtbaarheid
 			//over van het onderwerp.
 			$zichtbaarheid=$this->getZichtbaarheid();
 		}
		$sPostQuery="
			INSERT INTO
				forum_post
			(
				tid, uid, tekst, datum, ip, zichtbaar
			)VALUES(
				".$this->getID().",
				'".LoginLid::instance()->getUid()."',
				'".ucfirst($tekst)."',
				'".getDateTime()."',
				'".$ip."',
				'".$zichtbaarheid."'
			);";

		if($db->query($sPostQuery)){
			//een mailtje sturen naar de pubcie om de boel bevestigd te krijgen
			if($this->needsModeration() AND !$filter->isSpam()){
				//bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht
	 			mail('pubcie@csrdelft.nl', 'Nieuw bericht in extern wacht op goedkeuring',
	 			 	"http://csrdelft.nl/communicatie/forum/onderwerp/".$this->getID()."\r\n".
	 			 	"\r\nDe inhoud van het bericht is als volgt: \r\n\r\n".str_replace('\r\n', "\n", $tekst)."\r\n\r\nEINDE BERICHT");
	 		}
	 		//de getalletjes updaten
	 		$this->recount();
	 		return $db->insert_id();
		}else{
			return false;
		}
	}
	/*
	 * Onderwerp verplaatsten
	 */
	function move($newCat){
		$db=MySql::instance();
		$newCat=(int)$newCat;
		if(!ForumCategorie::existsVoorUser($newCat)){
			return false;
		}
		$sMove="
			UPDATE forum_topic
			SET categorie=".$newCat."
			WHERE id=".$this->getID()."
			LIMIT 1;";
		if($db->query($sMove) AND $this->getCategorie()->recount()){
			//nieuwe categorie ook hertellen.
			$this->setCategorie($newCat);
			return $this->getCategorie(true)->recount();
		}else{
			return false;
		}
	}
	/*
	 * Onderwerptitels bewerken.
	 */
	function rename($newTitel){
		if(!$this->isModerator()){
			return false;
		}
		$db=MySql::instance();
		$newTitel=$db->escape(trim($newTitel));
		$sRename="
			UPDATE forum_topic
			SET titel='".$newTitel."'
			WHERE id=".$this->getID()."
			LIMIT 1;";
		return $db->query($sRename);
	}


	//posts bewerken
	public function editPost($iPostID, $sBericht, $reden=''){
		$db=MySql::instance();

		//kijken of er wel iets aangepast is.
		$oldPost=$this->getSinglePost($iPostID);
		if($sBericht!=$oldPost['tekst']){
			$bewerkt='bewerkt door [lid='.LoginLid::instance()->getUid().'] [reldate]'.getDateTime().'[/reldate]';

			if($reden!=''){
				$bewerkt.=': '.$db->escape($reden);
			}
			$bewerkt.="\n";
			$sEditQuery="
				UPDATE
					forum_post
				SET
					tekst='".$db->escape($sBericht)."',
					bewerkDatum='".getDateTime()."',
					bewerkt=CONCAT(bewerkt, '".$bewerkt."')
				WHERE
					id=".$iPostID."
				LIMIT 1;";
			return $db->query($sEditQuery);
		}else{
			return true;
		}

	}

	//post 'verwijderen'.
	function deletePost($iPostID){
		$iPostID=(int)$iPostID;
		//hele onderwerp wegkekken als er maar één bericht is.
		if($this->getSize(true)==1){
			return $this->delete();
		}
		$sDeletePost="
			UPDATE forum_post
			SET zichtbaar='verwijderd'
			WHERE id=".$iPostID."
			LIMIT 1;";
		if(Mysql::instance()->query($sDeletePost)){
			return $this->recount();
		}else{
			return false;
		}
	}


	function delete(){
		$db=MySql::instance();
		$deletePosts="UPDATE forum_post SET zichtbaar='verwijderd' WHERE tid=".$this->getID().";";
		$deleteOnderwerp="UPDATE forum_topic SET zichtbaar='verwijderd' WHERE id=".$this->getID()." LIMIT 1;";
		$this->ID=0;
		return $db->query($deletePosts) AND $db->query($deleteOnderwerp) AND $this->getCategorie()->recount();
	}

	function toggleOpenheid(){
		$status=$this->isOpen() ? '0' : '1';
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				open='".$status."'
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		return MySql::instance()->query($sTopicQuery);
	}

	function togglePlakkerigheid(){
		$status=$this->isPlakkerig() ? '0' : '1';
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				plakkerig='".$status."'
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		return MySql::instance()->query($sTopicQuery);
	}

	public function keurGoed($iPostID){
		$db=MySql::instance();
		$iPostID=(int)$iPostID;

		$sPostQuery="
			UPDATE
				forum_post
			SET
				zichtbaar='zichtbaar'
			WHERE
				id=".$iPostID."
			LIMIT 1;";
		$sTopicQuery="
			UPDATE
				forum_topic
			SET
				zichtbaar='zichtbaar'
			WHERE
				id=".$this->getID()."
			LIMIT 1;";
		//queries uitvoeren en stats voor topic opnieuw berekenen
		return $db->query($sPostQuery) AND $db->query($sTopicQuery) AND $this->recount();
	}

	//dingen updaten voor het huidige topic
	public function recount(){
		$db=MySql::instance();
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
		$aTopicStats=$db->getRow($sTopicStats);
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
		return $db->query($sTopicUpdate) AND $this->getCategorie()->recount();
	}
	public function getError(){ return $this->error; }
}
