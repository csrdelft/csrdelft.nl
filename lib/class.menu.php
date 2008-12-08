<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.menu.php
# -------------------------------------------------------------------
# Een menu incl permissies uit de database trekken.
# De menuopties die niet overeenkomen met de permissies die de
# gebruiker heeft worden niet getoond.
# -------------------------------------------------------------------

require_once 'class.csrsmarty.php';

class menu {
	protected $_lid;
	protected $_db;

	//menu is een array met menu-opties.
	private $_menu=array();
	
	//huidig is het ID van de menu-optie waar we nu zijn.
	private $_huidig=1;
	//huidigTop is het ID van de menu-optie waaronder de huidige valt
	private $_huidigTop=0;

	private $_prefix;
	
	/**
		Michel: param $mainid toegevoegd, de vorminsbank heeft een ander hoofdmenu pID (= 99)
	**/
	public function menu($prefix='', $mainid=0) {
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
	
		$this->_menu=array();
		
		//ff de request_url van #name en .php ontdoen.
		$request_uri_full=$request_uri=$_SERVER['REQUEST_URI'];
		$dotphp=strpos($request_uri, '.php');
		if($dotphp!==false){ $request_uri=substr($request_uri, 0, $dotphp); }
		$sharp=strpos($request_uri, '#');
		if($sharp!==false){ $request_uri=substr($request_uri, 0, $sharp); }	

		# menu ophalen
		$sMenu="
			SELECT  
				ID, pID, tekst, link, permission
			FROM 
				menu 
			WHERE 
				zichtbaar='ja' 
			ORDER BY 
				pID ASC, prioriteit ASC, tekst ASC";
		$rMenu=$this->_db->query($sMenu);
		
		//Nu hier een boom-array maken.
		while($aMenu=$this->_db->next($rMenu)){
			//uitzoeken of de huidige pagina overeenkomt met de opgehaalde rij
			$bHuidig=false;
			if(	($aMenu['link']=='/' AND $request_uri=='/') OR
					($request_uri==$aMenu['link'] AND $aMenu['link']!='/') OR
					($request_uri_full==$aMenu['link'] AND $aMenu['link']!='/') OR
					(strpos($request_uri, $aMenu['link'])!==false AND $aMenu['link']!='/')){
				$this->_huidig=$aMenu['ID'];
				if($aMenu['pID']!=$mainid){ $this->_huidigTop=$aMenu['pID']; } //mw: 0 --> $mainid
				$bHuidig=true;
			}
			if($aMenu['pID']==$mainid){  //mw: 0 --> $mainid
				//hoofdniveau
				$this->_menu[$aMenu['ID']]=array(
					'ID' => $aMenu['ID'],
					'pID' => $aMenu['pID'],
					'tekst' => $aMenu['tekst'],
					'link' => $aMenu['link'],
					'subitems' => array(),
					'huidig' => $bHuidig,
					'rechten' => $aMenu['permission'] );
			}else{
				// Als een submenuitem huidig is, eventuele voorgaande submenuitems huidig=0 maken, om dubbele huidigen te voorkomen
				if ($bHuidig) {
					foreach ($this->_menu[$aMenu['pID']]['subitems'] as $key => $dummy) {
						$this->_menu[$aMenu['pID']]['subitems'][$key]['huidig'] = 0;
					}
				}
				
				//subniveau
				$this->_menu[$aMenu['pID']]['subitems'][$aMenu['ID']]=array(
					'ID' => $aMenu['ID'],
					'pID' => $aMenu['pID'],
					'tekst' => $aMenu['tekst'],
					'link' => $aMenu['link'],
					'subitems' => array(),
					'huidig' => $bHuidig,
					'rechten' => $aMenu['permission'] );
			}
		}

		//Prefix opslaan
		$this->_prefix=$prefix;
	}

	
	//viewWaarbenik gebruikt de menu array en $this->_huidig om een paadje te tekenen waar men is. 
	public function viewWaarbenik(){ 
		echo '&raquo; ';
		if($this->_huidig!=1){
			if(isset($this->_menu[$this->_huidig])){
				//één niveau diep: enkel de pagina zelf weergeven, met thuis als link ervoor
				echo ' '.$this->_menu[$this->_huidig]['tekst'];
			}else{
				//twee niveau's diep. Thuis link, hoofd-categorie link, sub-categorie
				$aTop=$this->_menu[$this->_huidigTop];
				echo '<a href="'.$aTop['link'].'">'.$aTop['tekst'].'</a> &raquo; ';
				echo $aTop['subitems'][$this->_huidig]['tekst'];
			}
		}else{
			echo 'Thuis';
		}
	}
	
	public function view() {
		$menu=new Smarty_csr();
		$menu->caching=false;
		
		$aMenuItems=array();
		$bHuidig=false;
		
		foreach($this->_menu as $aMenuItem){
			//controleer of de gebruiker wel het recht heeft om dit item te zien
			if(!$this->_lid->hasPermission($aMenuItem['rechten'])) continue;

			if($aMenuItem['huidig']){$bHuidig=true;}
			
			$aSubItems=array();
			foreach($aMenuItem['subitems'] as $aSubItem){
				if(!$this->_lid->hasPermission($aSubItem['rechten'])) continue;
				
				$aSubItems[]=$aSubItem;
			}			
			
			$aMenuItem['subitems']=$aSubItems;
			$aMenuItems[] = $aMenuItem;
		}
		
		//Als er geen huidig item is gekozen wordt het eerste menu huidig
		//if($bHuidig===false){$aMenuItems[0]['huidig']=true;}
		
		$menu->assign('items', $aMenuItems);		
		$menu->display($this->_prefix.'menu.tpl');
	}
	
	public static function getGaSnelNaar(){
		//hier worden even de objecten lokaal gemaakt, anders moet er voor dit ding ook nog een 
		//tweede instantie van Menu gemaakt worden.
		$lid=Lid::get_lid();
		$db=MySql::get_MySql();
		
		$gasnelnaar="SELECT tekst, link, permission FROM menu WHERE gasnelnaar='ja' ORDER BY tekst;";
		$result=$db->query($gasnelnaar);
		$return='<h1>Ga snel naar</h1>';
		if($result!==false AND $db->numRows($result)>0){
			while($gsn=$db->next($result)){
				if($lid->hasPermission($gsn['permission'])){
					$return.='<div class="item">&raquo; <a href="'.$gsn['link'].'">'.$gsn['tekst'].'</a></div>';
				}
			}
		}else{
			$return.='<div class="item">Geen items gevonden.</div>';
		}
		return $return;
	}
}

?>
