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
	private $_lid;
	
	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	var $_body;
	//menu bevat een menu-object.
	var $_menu;
	//standaard geen zijkolom...
	var $_zijkolom=false;
	
	private $_stylesheets=array();
	private $_scripts=array();
	
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
	
	function addStylesheet($sheet){ $this->_stylesheets[]=$sheet; }
	function getStylesheets(){		return $this->_stylesheets; }
	
	function addScript($script){ 	$this->_scripts[]=$script; }
	function getScripts(){			return $this->_scripts; }
	
	function getTitel(){ return mb_htmlentities($this->_titel); }
	function setZijkolom($zijkolom){
		if(is_object($zijkolom)){
			$this->_zijkolom=$zijkolom;
		}
	}
	function getBreed(){
		if($this->_zijkolom===false){ echo 'Breed'; }else{ echo ''; }
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);
		
		//SocCie-saldi, MaalCie-saldi
		$saldi=$this->_lid->getSaldi();
		$csrdelft->assign('saldi', $saldi);
		
		$csrdelft->caching=false;
		$csrdelft->display('csrdelft.tpl');
		
		if(defined('DEBUG') AND $this->_lid->hasPermission('P_ADMIN')){
			$db=MySql::get_MySql();
			echo '<pre>'.$db->getDebug().'</pre>';
		}
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
