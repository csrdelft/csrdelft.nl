<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
#
# -------------------------------------------------------------------
# Historie:
# 18-12-2004 Hans van Kranenburg
# . aangemaakt
#

require_once('class.simplehtml.php');

class csrdelft extends SimpleHTML {

	### private ###
	var $_lid;
	
	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	var $_body;
	//menu bevat een menu-object.
	var $_menu;
	//standaard geen zijkolom...
	var $_zijkolom=false;
	
	var $_titel='Geen titel gezet.';
	var $_waarbenik=false;
	
	function csrdelft($body, &$lid, &$db){
		if(is_object($body)){
			$this->_body=$body;
			//als de body een methode heeft om een titel mee te geven die gebruiken, anders de standaard.
			if(method_exists($this->_body, 'getTitel')){
				$this->_titel=$this->_body->getTitel();
			}
		}
		$this->_lid=&$lid;
		//nieuw menu-object aanmaken...
		require_once('class.menu.php');
		$this->_menu=new menu(&$lid, &$db);
		
	}
	
	function getTitel(){ return mb_htmlentities($this->_titel); }
	function setZijkolom($zijkolom){
		if(is_object($zijkolom)){
			$this->_zijkolom=$zijkolom;
		}
	}
	function getBreed(){
		if($this->_zijkolom===false){ echo 'Breed'; }else{ echo ''; }
	}
	
	function viewWaarbenik(){
		if(is_object($this->_waarbenik)){
			echo 'bla';
		}elseif(method_exists($this->_body, 'viewWaarbenik')){
			echo '&raquo; ';
			$this->_body->viewWaarbenik();
		}else{
			//uit de menu-array halen
			$this->_menu->viewWaarbenik();
		}
	}
	function view() {
		header('Content-Type: text/html; charset=UTF-8');
		$profiel=new Smarty_csr();
		$profiel->assign_by_ref('csrdelft', $this);
		
		//soccie saldi
		$saldi=$this->_lid->getSaldi($this->_lid->getUid(), true);
		$profiel->assign('saldi', $saldi);
		
		$profiel->caching=false;
		
		$profiel->display('csrdelft.tpl');
		
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
