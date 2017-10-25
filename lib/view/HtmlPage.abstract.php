<?php

namespace CsrDelft\view;

/**
 * HtmlPage.abstract.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een HTML pagina met stylesheets en scripts.
 *
 */
abstract class HtmlPage implements View {

	/**
	 * <BODY>
	 * @var View
	 */
	protected $body;
	/**
	 * <TITLE>
	 * @var string
	 */
	protected $titel;
	/**
	 * <CSS>
	 * @var array
	 */
	private $stylesheets = array();
	/**
	 * <SCRIPT>
	 * @var array
	 */
	private $scripts = array();

	public function __construct(View $body, $titel) {
		$this->body = $body;
		$this->titel = $titel;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBody() {
		return $this->body;
	}

	public function getModel() {
		return null;
	}

	/**
	 * Zorg dat de HTML pagina een stylesheet inlaadt. Er zijn twee verianten:
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
	 * Zorg dat de HTML pagina een script inlaadt. Er zijn twee verianten:
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
