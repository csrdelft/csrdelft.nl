<?php

require_once 'MVC/view/TemplateView.abstract.php';

/**
 * HtmlPage.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Een HTML pagina met stylesheets en scripts.
 * 
 */
abstract class HtmlPage extends TemplateView {

	private $stylesheets = array();
	private $scripts = array();

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
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/ toe te voegen.
	 */
	public function addStylesheet($sheet, $localpath = '/layout/') {
		if (startsWith($sheet, 'http')) {
			//extern
			$add = array(
				'naam' => $sheet,
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
			$this->stylesheets[$add['naam']] = $add;
		}
	}

	public function hasStylesheet($name) {
		return array_key_exists($name, $this->stylesheets);
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
	 * Buiten de huidige server, gewoon een url dus. Google jsapi
	 * bijvoorbeeld.
	 *
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/ toe te voegen.
	 */
	public function addScript($script, $localpath = '/layout/') {
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
			$this->scripts[$add['naam']] = $add;
		}
	}

	public function hasScript($naam) {
		return array_key_exists($naam, $this->scripts);
	}

	public function getScripts() {
		return $this->scripts;
	}

}
