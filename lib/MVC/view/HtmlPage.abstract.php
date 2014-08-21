<?php

require_once 'MVC/view/SmartyTemplateView.abstract.php';

/**
 * HtmlPage.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Een HTML pagina met stylesheets en scripts.
 * 
 */
abstract class HtmlPage extends SmartyTemplateView {

	private $stylesheets = array();
	private $scripts = array();

	/**
	 * Zorg dat de template een stylesheet inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addStylesheet($sheet, $remote = false) {
		if (!$remote) {
			$sheet .= '?' . filemtime(HTDOCS_PATH . $sheet);
		}
		$this->stylesheets[md5($sheet)] = $sheet;
	}

	public function getStylesheets() {
		return $this->stylesheets;
	}

	/**
	 * Zorg dat de template een script inlaadt. Er zijn twee verianten:
	 *
	 * - lokaal:
	 * een timestamp van de creatie van het bestand wordt toegoevoegd,
	 * zodat de browsercache het bestand vernieuwt.
	 *
	 * - extern:
	 * Buiten de huidige server, gewoon een url dus.
	 */
	public function addScript($script, $remote = false) {
		if (!$remote) {
			$script .= '?' . filemtime(HTDOCS_PATH . $script);
		}
		$this->scripts[md5($script)] = $script;
	}

	public function getScripts() {
		return $this->scripts;
	}

}
