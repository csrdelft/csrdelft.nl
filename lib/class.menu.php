<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.dbmenu.php
# -------------------------------------------------------------------
# Een menu incl permissies uit de database trekken.
# De menuopties die niet overeenkomen met de permissies die de
# gebruiker heeft worden niet getoond.
# -------------------------------------------------------------------
# Historie:
# 02-10-2006 Jieter
# . gemaakt
#

class menu {

	### private ###

	var $_lid;
	var $_db;

	//menu is een array met menu-opties.
	var $_menu=array();
	
	//huidig is het ID van de menu-optie waar we nu zijn.
	var $_huidig=1;
	//huidigTop is het ID van de menu-optie waaronder de huidige valt
	var $_huidigTop=0;
	
	### public ###

	function menu(&$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;
	
		$this->_menu=array();
		
		# menu ophalen
		$sMenu="
			SELECT  
				ID, pID, tekst, link
			FROM 
				_menu 
			WHERE 
				zichtbaar='ja' 
			ORDER BY 
				pID ASC, prioriteit ASC, tekst ASC";
		$rMenu=$this->_db->query($sMenu);
		$bUitgedeeld=false;
		//Nu hier een boom-array maken.
		while($aMenu=$this->_db->next($rMenu)){
			//uitzoeken of de huidige pagina overeenkomt met de opgehaalde rij
			$bHuidig=false; 
			if($bUitgedeeld==false){
				if(($aMenu['link']!='/' AND $_SERVER['REQUEST_URI']==$aMenu['link']) 
				//OR ($aMenu['link']!='/' AND strpos($_SERVER['REQUEST_URI'], $aMenu['link'])!==false)
					){
					$bHuidig=true; $bUitgedeeld=true;
					$this->_huidig=$aMenu['ID'];
					if($aMenu['pID']!=0){ $this->_huidigTop=$aMenu['pID']; }
				}
			}
			if($aMenu['pID']==0){
				//hoofdniveau
				$this->_menu[$aMenu['ID']]=array(
					'ID' => $aMenu['ID'],
					'pID' => $aMenu['pID'],
					'tekst' => $aMenu['tekst'],
					'link' => $aMenu['link'],
					'subitems' => array(),
					'huidig' => $bHuidig );
			}else{
				$this->_menu[$aMenu['pID']]['subitems'][$aMenu['ID']]=array(
					'ID' => $aMenu['ID'],
					'pID' => $aMenu['pID'],
					'tekst' => $aMenu['tekst'],
					'link' => $aMenu['link'],
					'subitems' => array(),
					'huidig' => $bHuidig );
			}
		}
		//standaard huidige pagina is de voorpagina met ID==1
		if($bUitgedeeld==false AND $_SERVER['REQUEST_URI'][0]=='/'){
			$this->_menu[1]['huidig']=true;
			$this->_huidig=1;
		}
	}

	
	//viewWaarbenik gebruikt de menu array en $this->_huidig om een paadje te tekenen waar men is. 
	function viewWaarbenik(){ 
		echo '&raquo; ';
		if($this->_huidig==1){
			echo 'Thuis';
		}else{
			if(isset($this->_menu[$this->_huidig])){
				//één niveau diep: enkel de pagina zelf weergeven, met thuis als link ervoor
				echo '<a href="/">Thuis</a> &raquo; '.$this->_menu[$this->_huidig]['tekst'];
			}else{
				//twee niveau's diep. Thuis link, hoofd-categorie link, sub-categorie
				$aTop=$this->_menu[$this->_huidigTop];
				echo '<a href="/">Thuis</a> &raquo; ';
				echo '<a href="'.$aTop['link'].'">'.$aTop['tekst'].'</a> &raquo; ';
				echo $aTop['subitems'][$this->_huidig]['tekst'];
			}
		}
	}
	
	function view() {
		echo '<div id="menu"><div id="menuContent">'."\r\n";
		$subMenu=0; $first=true;

		foreach($this->_menu as $aMenuItem){
			if(!$first){ echo ' | '; }else{ $first=false; }
			echo '<a href="'.$aMenuItem['link'].'">';
			if($aMenuItem['huidig']===true){ echo '<strong>'; }
			echo $aMenuItem['tekst'];
			if($aMenuItem['huidig']===true){ echo '</strong>'; }
			echo '</a>';
			if($aMenuItem['huidig']===true){ $subMenu=$aMenuItem['ID']; }
		}
		echo '</div>';
		if($subMenu==0){ $subMenu=$this->_huidigTop;}
		
		echo '<div id="submenuContent">';
		if($subMenu!=0){
			$first=true;
			
			foreach($this->_menu[$subMenu]['subitems'] as $aMenuItem){
				if(!$first){ echo ' - '; }else{ echo '[ '; $first=false; }
				echo '<a href="'.$aMenuItem['link'].'">';
				if($aMenuItem['huidig']===true){ echo '<strong>'; }
				echo $aMenuItem['tekst'];
				if($aMenuItem['huidig']===true){ echo '</strong>'; }
				echo '</a>';
			}
		}
		echo '</div></div>';
	}
}

?>
