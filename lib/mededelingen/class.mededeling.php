<?php
/*
 * class.mededeling.php	|  Maarten Somhorst
 *
 *
 */

require_once('class.mededelingcategorie.php');

class Mededeling{

	private $id;
	private $titel;
	private $tekst;
	private $datum;
	private $uid;
	private $rank;
	private $prive=0;
	private $verborgen=0;
	private $plaatje='';
	private $categorieId=0;
	private $categorie=null;

	const defaultRank=255;

	public function __construct($init){
		if(is_array($init)){
			$this->array2properties($init);
		}else{
			$init=(int)$init;
			if($init!=0){
				$this->load($init);
			}else{
				//default waarden voor een nieuwe mededeling
				$this->datum=getDateTime();
				$this->uid=LoginLid::instance()->getUid();
				$this->rank=self::defaultRank;
			}
		}
	}
	public function load($id=0){
		$db=MySql::instance();
		$loadQuery="
			SELECT id, titel, tekst, datum, uid, rank, prive, verborgen, plaatje, categorie
			FROM mededeling
			WHERE id=".(int)$id.";";
		$mededeling=$db->getRow($loadQuery);
		if(!is_array($mededeling)){
			throw new Exception('Mededeling bestaat niet. (Mededeling::load())');
		}
		$this->array2properties($mededeling);
	}
	public function save(){
		$db=MySql::instance();
		if($this->getId()==0){
			$saveQuery="
				INSERT INTO mededeling (
					titel, tekst, datum, uid, rank, prive, verborgen, categorie
				)VALUES(
					'".$db->escape($this->getTitel())."',
					'".$db->escape($this->getTekst())."',
					'".$this->getDatum()."',
					'".$this->getUid()."',
					".(int)$this->getRank().",
					'".(int)$this->getPrive()."',
					'".(int)$this->getVerborgen()."',
					".(int)$this->getCategorieId()."
				);";
		}else{
			$saveQuery="
				UPDATE
					mededeling
				SET
					titel='".$db->escape($this->getTitel())."',
					tekst='".$db->escape($this->getTekst())."',
					datum='".$this->getDatum()."',
					uid='".$this->getUid()."',
					rank=".(int)$this->getRank().",
					prive='".(int)$this->getPrive()."',
					verborgen='".(int)$this->getVerborgen()."',
					categorie=".(int)$this->getCategorieId()."
				WHERE
					id=".$this->getId()."
				LIMIT 1;";
		}
		$return=$db->query($saveQuery);

		if($return AND $this->getId()==0){
			$this->id=$db->insert_id();
		}
		return $return;
	}
	private function array2properties($array){
		$this->id=$array['id'];
		$this->titel=$array['titel'];
		$this->tekst=$array['tekst'];
		$this->datum=$array['datum'];
		$this->uid=$array['uid'];
		$this->rank=$array['rank'];
		$this->prive=$array['prive'];
		$this->verborgen=$array['verborgen'];
		$this->plaatje=$array['plaatje'];
		$this->categorieId=$array['categorie'];
	}
	public function getId(){ return $this->id; }
	public function getTitel(){ return $this->titel; }
	public function getTekst(){ return $this->tekst; }
	public function getDatum(){ return $this->datum; }
	public function getUid(){ return $this->uid; }
	public function getRank(){ return $this->rank; }
	public function getPrive(){ return $this->prive; }
	public function isPrive(){ return $this->getPrive==1; }
	public function getVerborgen(){ return $this->verborgen; }
	public function isVerborgen(){ return $this->getVerborgen()==1; }
	public function getPlaatje(){ return $this->plaatje; }
	public function getCategorieId(){ return $this->categorieId; }
	public function getCategorie($force=false){
		if($force OR $this->categorie===null){
			$this->categorie=new MededelingCategorie($this->getCategorieId());
		}
		return $this->categorie;
	}

	public function isMod(){
		return LoginLid::instance()->hasPermission('P_NEWS_MOD');
	}

	public static function getTopmost(){
		$db=MySql::instance();
		$top1query="
			SELECT id
			FROM mededeling
			WHERE rank = '1' AND verwijderd='0' AND verborgen='0'
			ORDER BY datum DESC, id DESC;";
		$top1=$db->getRow($top1query);
		if(is_array($top1)){
			return new Mededeling($top1['id']);
		}
		return Mededeling::getNewest();
	}

	public static function getNewest(){
		$db=MySql::instance();
		$newestQuery="
			SELECT id
			FROM mededeling
			WHERE rank = '1' AND verwijderd='0' AND verborgen='0'
			ORDER BY datum DESC, id DESC;";
		$newest=$db->getRow($newestQuery);
		if(is_array($newest)){
			return new Mededeling($newest['id']);
		}
		return null;
	}
	public function resetrank(){
		$updateRank="
			UPDATE mededeling
			SET	rank='".Mededeling::defaultRank."'
			WHERE rank='".$this->getRank()."';";
		return MySql::instance()->query($updateRank);
	}
	public static function getRanks(){
		$ranks=array();
		for($i=1; $i<=6; $i++){
			$ranks[$i]='Top '.$i;
		}
		return $ranks;
	}
}


?>
