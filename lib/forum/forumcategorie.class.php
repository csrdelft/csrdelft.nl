<?php
/*
 * class.forumcategorie.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'forumonderwerp.class.php';

class ForumCategorie{

	private $ID;
	private $naam;
	private $rechten_read;
	private $rechten_post;

	private $pagina=1;

	private $size=null;
	private $onderwerpen=null;

	protected $error;

	public function __construct($catID, $pagina=1){
		$this->ID=(int)$catID;
		$this->pagina=(int)$pagina;

		$this->load();
	}
	public function load(){
		$db=MySql::instance();

		$sTopicQuery="
			SELECT titel, rechten_read, rechten_post
			FROM forum_cat
			WHERE id=".$this->getID()."
			LIMIT 1;";
		$categorie=$db->getRow($sTopicQuery);
		if(is_array($categorie)){
			$this->naam=$categorie['titel'];
			$this->rechten_read=$categorie['rechten_read'];
			$this->rechten_post=$categorie['rechten_post'];

		}else{
			$this->error='Categorie bestaat niet';
			$this->ID=0;
			return false;
		}
	}
	public function loadOnderwerpen(){
		$db=MySql::instance();

		//ook op bevestiging wachtende berichten van niet ingelogde gebruikers zichtbaar maken
		//voor moderators
		if(LoginLid::instance()->hasPermission('P_FORUM_MOD')){
			$zichtBaarClause="( topic.zichtbaar='zichtbaar' OR topic.zichtbaar='wacht_goedkeuring' )";
		}else{
			$zichtBaarClause="topic.zichtbaar='zichtbaar'";
		}
		$onderwerpen="
			SELECT
				id, titel, categorie, uid, datumtijd, lastuser, lastpost, lastpostID,
				reacties, plakkerig, open, zichtbaar
			FROM
				forum_topic topic
			WHERE
				topic.categorie=".$this->getID()."
			AND
				".$zichtBaarClause."
			ORDER BY
				topic.plakkerig,
				topic.lastpost DESC
			LIMIT
				".($this->pagina-1)*Forum::getTopicsPerPagina().", ".Forum::getTopicsPerPagina().";";
		$result=$db->query($onderwerpen);

		if($db->numRows($result)>0){
			while($onderwerp=$db->next($result)){
				//maak allemaal nieuwe objectjes aan, maar laad geen posts in.
				$this->onderwerpen[]=new ForumOnderwerp($onderwerp, false);
			}
		}else{
			return false;
		}
	}

	public function getID(){			return $this->ID; }
	public function getNaam(){			return $this->naam; }
	public function getRechten_read(){	return $this->rechten_read; }
	public function magBekijken(){		return LoginLid::instance()->hasPermission($this->getRechten_read()); }
	public function getRechten_post(){	return $this->rechten_post; }
	public function magPosten(){ 		return LoginLid::instance()->hasPermission($this->getRechten_post()); }

	public function getPagina(){		return $this->pagina; }

	public function getSize($force=false){
		if($this->size===null OR $force===true){
			$query="SELECT count(*) AS aantal FROM forum_topic WHERE categorie=".$this->getID()." LIMIT 1";
			$size=MySql::instance()->getRow($query);
			$this->size=$size['aantal'];
		}
		return $this->size;
	}
	function getPaginaCount(){
		$db=MySql::instance();
		$sCatQuery="
			SELECT count(*) as aantal
			FROM forum_topic
			WHERE categorie=".$this->getID()."
			LIMIT 1;";
		$cat=$db->getRow($sCatQuery);
		if(is_array($cat)){
			$aantal=ceil($cat['aantal']/Forum::getTopicsPerPagina());
			if($aantal>0){
				return $aantal;
			}
		}
		return 1;
	}
	public function getOnderwerpen(){
		if($this->onderwerpen===null){
			$this->loadOnderwerpen();
		}
		if(is_array($this->onderwerpen)){
			return $this->onderwerpen;
		}
		return false;
	}
	public function recount(){
		$db=MySql::instance();
		$sCatStats="
			SELECT
				id, lastuser, lastpostID, lastpost
			FROM
				forum_topic
			WHERE
				categorie=".$this->getID()." AND
				zichtbaar='zichtbaar'
			ORDER BY
				lastpost DESC
			LIMIT 1;";
		$rCatStats=$db->query($sCatStats);
		if($db->numRows($rCatStats)==1){
			$aCatStats=$db->next($rCatStats);
			//subqueries voor aantal reacties en aantal topics
			$reacties="(SELECT SUM(reacties) AS aantal FROM forum_topic WHERE categorie=".$this->getID()." GROUP BY categorie LIMIT 1)";
			$topics="(".$this->getSize(true).")";
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
				id=".$this->getID()."
			LIMIT 1;";
		return $db->query($sCatUpdate);
	}

	public static function existsVoorUser($iCatID){
		$categorie=new ForumCategorie((int)$iCatID);
		if($categorie->getID()!=0){
			return LoginLid::instance()->hasPermission($categorie->getRechten_read());
		}
		return false;
	}
	//categorieen gesorteerd op volgorde
	public static function getAll($voorLid=false){
		$db=MySql::instance();
		$sCatsQuery="
			SELECT
				id, titel, beschrijving, lastuser, lastpost, lasttopic, lastpostID, reacties, topics, rechten_read
			FROM forum_cat
			WHERE zichtbaar=1
			ORDER BY volgorde;";
		$rCatsResult=$db->query($sCatsQuery);
		$lid=LoginLid::instance();
		while($aCat=$db->next($rCatsResult)){
			if($voorLid===true AND !$lid->hasPermission($aCat['rechten_read'])){
				continue;
			}
			$aCats[]=$aCat;
		}
		return $aCats;
	}
	public function __toString(){
		return (string)$this->naam;
	}
}

?>
