<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrdelft2.class.php
# -------------------------------------------------------------------
# csrdelft2 is de nieuwe layout
# -------------------------------------------------------------------

class csrdelft2 extends SimpleHTML {

	private $_body;
	private $_stylesheets = array();
	private $_scripts = array();

	function __construct(SimpleHTML $body) {
		$this->_body = $body;
		$this->addStylesheet('style.css');
		$this->addStylesheet('foundation.css');
		$this->addStylesheet('normalize.css');
		$this->addScript('jquery.js');
		$this->addScript('jquery.backstretch.js');
		$this->addScript('jquery.timeago.js', '/layout/');
		$this->addScript('init.js');
	}

	/**
	 * Zorg dat de template een stijl inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 *
	 * Merk op: local-entry wordt ook gebruikt om een map buiten /layout2/ toe te voegen.
	 */
	function addStylesheet($sheet, $localpath = '/layout2/') {
		if (startsWith($sheet, 'http')) {
			//extern
			$add = array(
				'naam' => $script,
				'local' => false,
				'datum' => ''
			);
		} else {
			//lokaal
			$add = array(
				'naam' => $localpath . $sheet,
				'local' => true,
				//voeg geen datum toe als er al een '?' in de scriptnaam staat
				'datum' => (strstr($sheet, '?') ? '' : filemtime(HTDOCS_PATH . $localpath . $sheet))
			);
		}
		if (!$this->hasStylesheet($add['naam'])) {
			$this->_stylesheets[$add['naam']] = $add;
		}
	}

	public function hasStylesheet($name) {
		return array_key_exists($name, $this->_stylesheets);
	}

	function getStylesheets() {
		return $this->_stylesheets;
	}

	/**
	 * Zorg dat de template een script inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus. Google jsapi
	 * bijvoorbeeld.
	 *
	 * Merk op: local-entry wordt ook gebruikt om een map buiten /layout2/ toe te voegen.
	 */
	function addScript($script, $localpath = '/layout2/') {
		if (startsWith($script, 'http')) {
			//extern
			$add = array(
				'naam' => $script,
				'local' => false,
				'datum' => ''
			);
		} else {
			//lokaal
			$add = array(
				'naam' => $localpath . 'js/' . $script,
				'local' => true,
				//voeg geen datum toe als er al een '?' in de scriptnaam staat
				'datum' => (strstr($script, '?') ? '' : filemtime(HTDOCS_PATH . $localpath . 'js/' . $script))
			);
		}
		if (!$this->hasScript($add['naam'])) {
			$this->_scripts[$add['naam']] = $add;
		}
	}

	public function hasScript($naam) {
		return array_key_exists($naam, $this->_scripts);
	}

	function getScripts() {
		return $this->_scripts;
	}

	/**
	 * $template zorgt ervoor dat we pagina's in verschillende templates kunnen renderen.
	 * Default is 'content' dat is de layout van de hoofdpagina voor de 2012-remake
	 *
	 * @param string $template naam van smarty template
	 */
	function view($template='content') {
		header('Content-Type: text/html; charset=UTF-8');
		$smarty = new Smarty_csr();
		$smarty->assign_by_ref('this', $this);

		// SocCie-saldi & MaalCie-saldi
		$smarty->assign('saldi', LoginLid::instance()->getLid()->getSaldi());

		if(method_exists($this->_body, 'getMenuTpl')){
			$smarty->assign('menutpl', $this->_body->getMenuTpl());
		}

		$smarty->assign('body', $this->_body);

		$smarty->display('csrdelft2/'. $template .'.tpl');

		// als er een error is geweest, die unsetten...
		if (isset($_SESSION['auth_error'])) {
			unset($_SESSION['auth_error']);
		}
	}

}
