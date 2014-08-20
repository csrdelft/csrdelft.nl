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
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/css/ toe te voegen.
	 */
	public function addStylesheet($sheet, $path = '/layout/css/') {
		if (!$this->hasStylesheet($sheet)) {
			if (!startsWith($sheet, 'http') AND strpos($sheet, '?') === false) {
				$sheet .= '?' . filemtime(HTDOCS_PATH . $path . $sheet);
			}
			$this->stylesheets[] = $path . $sheet;
		}
	}

	public function hasStylesheet($sheet) {
		return array_key_exists($sheet, $this->stylesheets);
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
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/js/ toe te voegen.
	 */
	public function addScript($script, $path = '/layout/js/') {
		if (!$this->hasStylesheet($script)) {
			if (!startsWith($script, 'http') AND strpos($script, '?') === false) {
				$script .= '?' . filemtime(HTDOCS_PATH . $path . $script);
			}
			$this->scripts[] = $path . $script;
		}
	}

	public function hasScript($script) {
		return array_key_exists($script, $this->scripts);
	}

	public function getScripts() {
		return $this->scripts;
	}

}
