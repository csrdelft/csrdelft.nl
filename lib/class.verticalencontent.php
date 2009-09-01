<?php

class Verticale{
	public static $namen=array('Geen', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

	public $nummer;
	public $naam;
	public $kringen=array();

	
	public function __construct($nummer, $kringen=array()){
		if(preg_match('/^[A-Z]{1}$/', $nummer)){
			$nummer=array_search($nummer, Verticale::$namen);
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
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') AND verticale=".$this->nummer."
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
			WHERE (status='S_NOVIET' OR status='S_GASTLID' OR status='S_LID' OR status='S_KRINGEL') AND verticale !=0
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
class VerticalenContent extends SimpleHTML{
	public function getTitel(){
		return 'Verticalen der Civitas';
	}
	public function viewEmails($vertkring){
		try{
			$verticale=new Verticale(substr($vertkring, 0, 1));
		}catch(Exception $e){
			echo 'Verticale bestaat niet';
			return false;
		}
		if($verticale instanceof Verticale){
			try{
				$kring=$verticale->getKring((int)substr($vertkring, 2, 1));
			}catch(Exception $e){
				echo 'Kring bestaat niet';
				return false;
			}
			$leden=array();
			foreach($kring as $kringlid){
				$leden[]=$kringlid->getEmail();
			}
			echo implode(', ', $leden);
		}
	}
	public function view(){
		$verticalen=Verticale::getAll();
		
		echo '<ul class="horizontal nobullets">
			<li>
				<a href="/communicatie/ledenlijst/">Ledenlijst</a>
			</li>
			<li>
				<a href="/communicatie/verjaardagen" title="Overzicht verjaardagen">Verjaardagen</a>
			</li>
			<li class="active">
				<a href="/communicatie/verticalen/">Kringen</a>
			</li>
		</ul>
		<hr />';
			
		foreach($verticalen as $verticale){
			
			echo '<div class="verticale">';
			echo '<h1>Verticale '.$verticale->getNaam().'</h1>';
			foreach($verticale->getKringen() as $kringnaam => $kring){
				$kringstyle='kring';
				if($kringnaam==0){
					$kringstyle='geenkring';
				}
				echo '<div class="'.$kringstyle.'" id="kring'.$verticale->getNaam().'.'.$kringnaam.'">';
				echo '<div class="mailknopje" onclick="toggleEmails(\''.$verticale->getNaam().'.'.$kringnaam.'\')">@</div>';
				if($kringnaam==0){
					echo '<h2>Geen kring</h2>'; 
				}else{
					echo '<h2>Kring '.$kringnaam.'</h2>';
				}
				echo '<div id="leden'.$verticale->getNaam().'.'.$kringnaam.'">';
				foreach($kring as $lid){
					if($lid->isKringleider()) echo '<em>';
					echo $lid->getNaamLink('full', 'link');
					if($lid->getStatus()=='S_KRINGEL') echo '&nbsp;~';
					if($lid->isVerticaan()) echo '&nbsp;L';
					if($lid->isKringleider()) echo '</em>';
					echo '<br />';
				}
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			
		}
		?>
		<script type="text/javascript">
			if(document.location.hash.substring(1,6)=='kring'){
				kring=document.location.hash.substring(1);
				document.getElementById(kring).style.backgroundColor='#f1f1f1';
			}
		</script>
		<?php
	}

}
?>
