<?php

class Verticale{
	public static $namen=array('Geen', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

	public $naam;
	public $kringen=array();

	
	public function __construct($nummer, $kringen=array()){
		if((int)$nummer!=$nummer){
			$nummer=array_search($nummer, $this->kringen);
		}
		$this->naam=Verticale::$namen[$nummer];
		
			
	}
	public function getNaam(){
		return $this->naam;
	}
	public function getKringen(){
		return $this->kringen;
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
	
	public function view(){
		$verticalen=Verticale::getAll();

		foreach($verticalen as $verticale){
			
			echo '<div class="verticale">';
			echo '<h1>Verticale '.$verticale->getNaam().'</h1>';
			foreach($verticale->getKringen() as $kringnaam => $kring){
				$kringstyle='kring';
				if($kringnaam==0){
					$kringstyle='geenkring';
				}
				echo '<div class="'.$kringstyle.'" id="kring'.$verticale->getNaam().'.'.$kringnaam.'">';
				
				if($kringnaam==0){
					echo '<h2>Geen kring</h2>'; 
				}else{
					echo '<h2>Kring '.$kringnaam.'</h2>';
				}
				foreach($kring as $lid){
					if($lid->isKringleider()) echo '<em>';
					echo $lid->getNaamLink('full', 'link');
					if($lid->getStatus()=='S_KRINGEL') echo '&nbsp;~';
					if($lid->isVerticaan()) echo '&nbsp;L';
					if($lid->isKringleider()) echo '</em>';
					echo '<br />';
				}
				echo '</div>';
			}
			echo '</div>';
			
		}
		?>
		<script type="text/javascript">
			if(document.location.hash.substring(1,6)=='kring'){
				kring=document.location.hash.substring(1);
				document.getElementById(kring).style.backgroundColor='#f1f1f1';
				//document.getElementById(kring+'leden').style.backgroundColor='lightblue';
			}
		</script>
		<?php
	}

}
?>
