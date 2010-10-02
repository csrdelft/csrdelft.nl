<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrdelft.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------

class csrdelft extends SimpleHTML {

	//body is een object met een view-methode welke de content van de pagina maakt.
	//Als body een methode zijKolom() heeft die gebruiken om de zij-kolom te vullen
	public $_body;
	//menu bevat een menu-object.
	public $_menu;

	/*
	 * Zijkolom is standaard, tenzij met setZijkolom($simplehtml); een ander object
	 * gezet wordt, of met setZijkolom(false); de zijkolom wordt uitgezet.
	 */
	public $_zijkolom=null;

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
		require_once('menu.class.php');
		$this->_menu=new menu($this->_prefix, $menuid);

		//Stylesheets en scripts die we altijd gebruiken
		if($this->_prefix=='pauper_'){
			$this->addStylesheet('pauper.css');
		}else{
			$this->addStylesheet('undohtml.css');
			$this->addStylesheet('default.css');
			$this->addScript('jquery.js');
			$this->addScript('csrdelft.js');
			$this->addScript('menu.js');
			
			if(Instelling::get('algemeen_sneltoetsen')=='ja'){
				$this->addScript('prototype.js');
				$this->addScript('sneltoetsen.js');
			}
		}
		
		//Roze webstek
		if(Instelling::get('layout_rozeWebstek')=='ja' AND LoginLid::instance()->getUid()!='x999'){
			$this->addStylesheet('roze.css');
		}
	}

	function addStylesheet($sheet){
		$this->_stylesheets[]=array(
			'naam' => $sheet,
			'local' => true,
			'datum' => filemtime(HTDOCS_PATH.'/layout/'.$sheet)
		);
	}
	function getStylesheets(){		return $this->_stylesheets; }


	/*
	 * Zorg dat de template een script inlaadt. Er zijn twee verianten:
	 * 
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd, 
	 * zodat de browsercache het bestand vernieuwt.
	 * 
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus. Google jsapi 
	 * bijvoorbeeld.
	 */
	function addScript($script){
		$localJsPath=HTDOCS_PATH.'/layout/js/';
				
		if(substr($script, 0, 7)=='http://'){
			//extern script
			$add=array(
				'naam' => $script,
				'local' => false,
				'datum' => ''
			);
		}else{
			//lokaal script
			$add=array(
				'naam' => $script,
				'local' => true,
				//voeg geen datum toe als er al een '?' in de scriptnaam staat
				'datum' => (strstr($script,'?')?'':filemtime($localJsPath.$script))
			);
		}
		if(!$this->hasScript($add['naam'])){
			$this->_scripts[]=$add;
		}
	}
	public function hasScript($filename){
		foreach($this->_scripts as $script){
			if($script['naam']==$filename){
				return true;
			}
		}
		return false;
	}
	function getScripts(){			return $this->_scripts; }

	function getTitel(){ return mb_htmlentities($this->_titel); }
	function setZijkolom($zijkolom=null){
		$this->_zijkolom=$zijkolom;
	}

	public function getDebug($sql=true, $get=true, $post=true, $files=false, $session=true, $cookie=true){
		$debug = '';
		if ($sql)         { $debug .= '<hr />SQL<hr />';     if (count($this->querys) > 0)	$debug .= '<pre>'.htmlentities(print_r(MySql::instance()->getQueries(), true)).'</pre>';     }
		if ($get)         { $debug .= '<hr />GET<hr />';     if (count($_GET) > 0)		$debug .= '<pre>'.htmlentities(print_r($_GET, true)).'</pre>';     }
		if ($post)        { $debug .= '<hr />POST<hr />';    if (count($_POST) > 0)		$debug .= '<pre>'.htmlentities(print_r($_POST, true)).'</pre>';    }
		if ($files)       { $debug .= '<hr />FILES<hr />';   if (count($_FILES) > 0)		$debug .= '<pre>'.htmlentities(print_r($_FILES, true)).'</pre>';   }
		if ($session)     { $debug .= '<hr />SESSION<hr />'; if (count($_SESSION) > 0)		$debug .= '<pre>'.htmlentities(print_r($_SESSION, true)).'</pre>'; }
		if ($cookie)      { $debug .= '<hr />COOKIE<hr />';  if (count($_COOKIE) > 0)		$debug .= '<pre>'.htmlentities(print_r($_COOKIE, true)).'</pre>';  }
		return $debug;
	}
	function view() {
		$loginlid=LoginLid::instance();

		//als $this->_zijkolom geen Kolom-object bevat en niet false is
		//$this->_zijkolom vullen met een standaard lege kolom.
		if($this->_zijkolom!==false AND !($this->_zijkolom instanceof Kolom)){
			$this->_zijkolom=new Kolom();
		}

		header('Content-Type: text/html; charset=UTF-8');
		$csrdelft=new Smarty_csr();
		$csrdelft->assign_by_ref('csrdelft', $this);

		//SocCie-saldi, MaalCie-saldi
		$csrdelft->assign('saldi', $loginlid->getLid()->getSaldi());

		if(defined('DEBUG') AND ($loginlid->hasPermission('P_ADMIN') OR $loginlid->isSued())){
			$csrdelft->assign('db', MySql::instance());
		}

		$csrdelft->caching=false;
		$csrdelft->display($this->_prefix.'csrdelft.tpl');

		//als er een error is geweest, die unsetten...
		if(isset($_SESSION['auth_error'])){ unset($_SESSION['auth_error']); }
	}

}

?>
