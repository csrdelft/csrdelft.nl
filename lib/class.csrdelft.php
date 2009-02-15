<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------


require_once('class.simplehtml.php');

class csrdelft extends SimpleHTML {

	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	public $_body;
	//menu bevat een menu-object.
	public $_menu;
	//standaard geen zijkolom...
	public $_zijkolom=false;

	private $_stylesheets=array();
	private $_scripts=array();

	private $_titel='Geen titel gezet.';
	private $_prefix;

	function __construct($body,$prefix='',$menuid=0){ //mw: param menuid toegevoegd, zodat het goede menu geladen wordt (voor vb=99)
		if(is_object($body)){
			$this->_body=$body;
			//als de body een methode heeft om een titel mee te geven die gebruiken, anders de standaard.
			if(method_exists($this->_body, 'getTitel')){
				$this->_titel=$this->_body->getTitel();
			}
		}
		//Prefix opslaan
		$this->_prefix=$prefix;
		if($this->_prefix=='' AND isset($_SESSION['pauper'])){
			$this->_prefix='pauper_';
		}

		//nieuw menu-object aanmaken...
		require_once('class.menu.php');
		$this->_menu=new menu($this->_prefix, $menuid);

		//Stylesheets en scripts die we altijd gebruiken
		if($this->_prefix=='pauper_'){
			$this->addStylesheet('pauper.css');
		}else{
			$this->addStylesheet('undohtml.css');
			$this->addStylesheet('default.css');
			$this->addScript('csrdelft.js');
			$this->addScript('menu.js');
		}

	}

	function addStylesheet($sheet){
		$this->_stylesheets[]=array(
			'naam' => $sheet,
			'datum' => filemtime(HTDOCS_PATH.'/layout/'.$sheet)
		);
	}
	function getStylesheets(){		return $this->_stylesheets; }

	function addScript($script){
		$this->_scripts[]=array(
			'naam' => $script,
			'datum' => (strstr($script,'?')?'':filemtime(HTDOCS_PATH.'/layout/'.$script))
		);
	}
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
		$lid=Lid::instance();

		header('Content-Type: text/html; charset=UTF-8');
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);

		//SocCie-saldi, MaalCie-saldi
		$saldi=$lid->getSaldi();
		$csrdelft->assign('saldi', $saldi);

		$csrdelft->caching=false;
		$csrdelft->display($this->_prefix.'csrdelft.tpl');

		if(defined('DEBUG') AND $lid->hasPermission('P_ADMIN')){
			$db=MySql::instance();
			echo '
			<h2 id="mysql_debug_header"><a id="mysql_debug_showhide" href="#mysql_debug_header" onclick="return  toggleDiv(\'mysql_debug\');">Debug Tonen/Verstoppen</a></h2>
			<div id="mysql_debug" style="display: none"><pre>'.$db->getDebug().'</pre></div>';
		}
		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
