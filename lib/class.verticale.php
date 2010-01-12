<?php

class Verticale{
	public static $namen=array('Geen', 'Archibald', 'Faculteit', 'C', 'Diagonaal', 'E', 'Lekker', 'Securis', 'H');
	public static $letters=array('Geen', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
	
	public $nummer;
	public $naam;
	public $kringen=array();

	
	public function __construct($nummer, $kringen=array()){
		if(preg_match('/^[A-Z]{1}$/', $nummer)){
			$nummer=array_search($nummer, Verticale::$letters);
		}

		if(!array_key_exists($nummer, Verticale::$namen)){
			throw new Exception('Verticale bestaat niet');
		}
		$this->nummer=$nummer;
		$this->naam=Verticale::$namen[$nummer];

	}
	public function loadKringen(){
		$db=MySql::instance();
		$query="
			SELECT kring, GROUP_CONCAT(uid ORDER BY kringleider DESC, achternaam ASC) as kringleden
			FROM lid
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') 
			  AND verticale=".$this->nummer."
			GROUP BY kring
			ORDER BY kring";
		$result=$db->query($query);
		while($row=$db->next($result)){
			$this->addKring($row['kring'], $row['kringleden']);
		}
	}
	public function getNaam(){
		return $this->naam;
	}
	public function getLetter(){
		return self::$letters[$this->nummer];
	}
	
	public function getKringen(){
		return $this->kringen;
	}
	
	public function getKring($kring){
		if(sizeof($this->kringen)==0){
			$this->loadKringen();
		}
		if(!array_key_exists($kring, $this->kringen)){
			throw new Exception('Kring bestaat niet');
		}
		return $this->kringen[$kring];
	}
	
	public function addKring($kring, $kringleden){
		$leden=explode(',', $kringleden);
		$this->kringen[$kring]=array();
		foreach($leden as $uid){
			$this->kringen[$kring][]=LidCache::getLid($uid);
		}
	}

		
	public static function getAll(){
		$db=MySql::instance();
		$query="
			SELECT verticale, kring, GROUP_CONCAT(uid ORDER BY kringleider DESC, achternaam ASC) as kringleden
			FROM lid
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') 
			  AND verticale !=0
			GROUP BY verticale, kring
			ORDER BY verticale, kring";
		$result=$db->query($query);
	
		$vID=0;
		$verticalen=array();
		
		while($row=$db->next($result)){
			if($vID!=$row['verticale']){
				$verticalen[]=$verticale;
				$vID=$row['verticale'];
				$verticale=new Verticale($vID);
			}
			$verticale->addKring($row['kring'], $row['kringleden']);
		}
		$verticalen[]=$verticale;
		unset($verticalen[0]);

		return $verticalen;
		
	}
	
}

?>
