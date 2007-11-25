<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class csrdelft extends SimpleHTML {

	### private ###
	//TODO: template vind het nodig dat dit public is, zou natuurlijk niet zo moeten zijn...
	public $_lid;
	
	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	var $_body;
	//menu bevat een menu-object.
	var $_menu;
	//standaard geen zijkolom...
	var $_zijkolom=false;
	
	public $stylesheets=array();
	
	var $_titel='Geen titel gezet.';
	var $_waarbenik=false;
	
	function csrdelft($body){
		if(is_object($body)){
			$this->_body=$body;
			//als de body een methode heeft om een titel mee te geven die gebruiken, anders de standaard.
			if(method_exists($this->_body, 'getTitel')){
				$this->_titel=$this->_body->getTitel();
			}
		}
		$this->_lid=Lid::get_lid();
		//nieuw menu-object aanmaken...
		require_once('class.menu.php');
		$this->_menu=new menu();
		
	}
	
	function addStylesheet($sheet){ $this->stylesheets[]=$sheet; }
	function getStylesheets(){
		$return='';
		foreach($this->stylesheets as $stylesheet){
			$return='<link rel="stylesheet" href="/layout/'.$stylesheet.'" type="text/css" />';
		}
		return $return;
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
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);
		
		//SocCie-saldi, MaalCie-saldi
		$saldi=$this->_lid->getSaldi($this->_lid->getUid(), true);
		$csrdelft->assign('saldi', $saldi);
		
		$csrdelft->caching=false;
		$csrdelft->display('csrdelft.tpl');
		
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
