<?php
/*
 * class.mededeling.php	|  Maarten Somhorst
 *
 *
 */

require_once('class.mededelingcategorie.php');

class Mededeling{

	private $id=0;
	private $datum;
	private $uid;
	private $titel;
	private $tekst;
	private $zichtbaarheid;
	private $prive=0;
	private $categorieId=0;
	private $prioriteit;
	private $plaatje='';

	private $categorie=null;

	const defaultPrioriteit=255;

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
				$this->prioriteit=self::defaultPrioriteit;
			}
		}
	}
	public function load($id=0){
		$db=MySql::instance();
		$loadQuery="
			SELECT id, datum, titel, tekst, categorie, uid, prioriteit, prive, zichtbaarheid, plaatje, categorie
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
					titel, tekst, datum, uid, prioriteit, prive, zichtbaarheid, categorie, plaatje
				)VALUES(
					'".$db->escape($this->getTitel())."',
					'".$db->escape($this->getTekst())."',
					'".$this->getDatum()."',
					'".$this->getUid()."',
					".(int)$this->getPrioriteit().",
					'".(int)$this->getPrive()."',
					'".$this->getZichtbaarheid()."',
					".(int)$this->getCategorieId().",
					'".$db->escape($this->getPlaatje())."'
				);";
		}else{
			// Only update the field plaatje if there is a new picture.
			// TODO: destroy the old picture! 
			$setPlaatje='';
			if($this->getPlaatje()!=''){
				$setPlaatje=",
					plaatje='".$db->escape($this->getPlaatje())."'";
			}
			$saveQuery="
				UPDATE
					mededeling
				SET
					titel='".$db->escape($this->getTitel())."',
					tekst='".$db->escape($this->getTekst())."',
					datum='".$this->getDatum()."',
					uid='".$this->getUid()."',
					prioriteit=".(int)$this->getPrioriteit().",
					prive='".(int)$this->getPrive()."',
					zichtbaarheid='".$this->getZichtbaarheid()."',
					categorie=".(int)$this->getCategorieId().
					$setPlaatje."
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
	/*
	 * Fills the fields of this object with the given array.
	 */
	private function array2properties($array){
		$this->id=$array['id'];
		$this->titel=$array['titel'];
		$this->tekst=$array['tekst'];
		$this->datum=$array['datum'];
		$this->uid=$array['uid'];
		$this->prioriteit=$array['prioriteit'];
		$this->prive=$array['prive'];
		$this->zichtbaarheid=$array['zichtbaarheid'];
		$this->plaatje=$array['plaatje'];
		$this->categorieId=$array['categorie'];
	}
	public function getId(){ return $this->id; }
	public function getTitel(){ return $this->titel; }
	public function getTekst(){ return $this->tekst; }
	public function getDatum(){ return $this->datum; } //TODO: leesbare datum teruggeven(??)
	public function getUid(){ return $this->uid; }
	public function getPrioriteit(){ return $this->prioriteit; }
	public function getPrive(){ return $this->prive; }
	public function isPrive(){ return $this->getPrive()==1; }
	public function getZichtbaarheid(){ return $this->zichtbaarheid; }
	public function isVerborgen(){ return $this->getZichtbaarheid()=='onzichtbaar'; }
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
			WHERE prioriteit = '1' AND verwijderd='0' AND zichtbaarheid='zichtbaar'
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
			WHERE prioriteit = '1' AND verwijderd='0' AND zichtbaarheid='zichtbaar'
			ORDER BY datum DESC, id DESC;";
		$newest=$db->getRow($newestQuery);
		if(is_array($newest)){
			return new Mededeling($newest['id']);
		}
		return null;
	}
	public function resetPrioriteit(){
		$updatePrioriteit="
			UPDATE mededeling
			SET	prioriteit='".Mededeling::defaultPrioriteit."'
			WHERE prioriteit='".$this->getPrioriteit()."';";
		return MySql::instance()->query($updatePrioriteit);
	}
	public static function getPrioriteiten(){
		$prioriteiten=array();
		$prioriteiten[255]='geen';
		for($i=1; $i<=6; $i++){
			$prioriteiten[$i]='Top '.$i;
		}
		return $prioriteiten;
	}
}


?>
