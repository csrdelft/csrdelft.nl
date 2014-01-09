<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrdelft.class.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezet
# -------------------------------------------------------------------
class csrdelft extends SimpleHTML {

	private $_body;
	private $_layout;
	private $_stylesheets = array();
	private $_scripts = array();

	/**
	 * De normale layout heeft een array van SimpleHTML als zijkolom
	 */
	public $zijkolom = array();

	function __construct(SimpleHTML $body, $layout = 'normaal') {
		$this->_body = $body;
		$this->_layout = $layout;
		
		switch($this->_layout) {

			case 'csrdelft2':
				$this->addStylesheet('style.css', '/layout2/');
				$this->addStylesheet('foundation.css', '/layout2/');
				$this->addStylesheet('normalize.css', '/layout2/');
				$this->addStylesheet('ubb.css', '/layout/');
				$this->addScript('jquery.js', '/layout2/');
				$this->addScript('jquery.backstretch.js', '/layout2/');
				$this->addScript('jquery.timeago.js');
				$this->addScript('init.js', '/layout2/');
				$this->addScript('csrdelft.js', '/layout/');
			return;

			case 'normaal':
			case 'owee':
			case 'lustrum':
			default:
				
				$this->addStylesheet('undohtml.css');
				$this->addStylesheet('ubb.css');
				$this->addStylesheet('csrdelft.css');
				$layout = Instelling::get('layout');
				if (!Instelling::hasEnumOption('layout', $layout)) { // fix verwijderde layout
					$layout = 'normaal';
					Instelling::set('layout', $layout);
					Instelling::save();
				}
				$this->addStylesheet($layout .'.css');
				if (Instelling::get('layout_beeld') == 'breedbeeld') {
					$this->addStylesheet('breedbeeld.css');
				}
				if (Instelling::get('layout_sneeuw') != 'nee') {
					if (Instelling::get('layout_sneeuw') == 'ja') {
						$this->addStylesheet('snow.anim.css');
					} else {
						$this->addStylesheet('snow.css');
					}
				}
				$this->addScript('jquery.min.js');
				$this->addScript('jquery.timeago.js');
				$this->addScript('jquery.hoverIntent.min.js');
				$this->addScript('csrdelft.js');
				$this->addScript('dragobject.js');
				$this->addScript('menu.js');
				
				if (Instelling::get('algemeen_sneltoetsen') == 'ja') {
					$this->addScript('sneltoetsen.js');
				}
			return;
		}
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
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/ toe te voegen.
	 */
	function addStylesheet($sheet, $localpath = '/layout/') {
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
	 * Merk op: local-entry kan ook gebruikt worden om een map buiten /layout/ toe te voegen.
	 */
	function addScript($script, $localpath = '/layout/') {
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
	 * Voor layout csrdelft2 zijn extra parameters nodig:
	 * @param string $template
	 * @param string $menutemplate
	 */
	function view($template = '', $menutemplate = '') {
		
		header('Content-Type: text/html; charset=UTF-8');
		$smarty = new Smarty_csr();
		$smarty->assignByRef('this', $this);
		$smarty->assign('body', $this->_body);
		
		switch($this->_layout) {

			case 'csrdelft2':
				if ($template === '') {
					$template = 'content';
				}
				if ($menutemplate !== '') {
					$smarty->assign('menutpl', $menutemplate);
				}
				$smarty->display('csrdelft2/'. $template .'.tpl');
			return;

			case 'normaal':
			case 'owee':
			case 'lustrum':
			default:
				if (Instelling::get('layout_minion') == 'ja') {
					$this->addStylesheet('minion.css');
					$this->addScript('minion.js');
					$top = 40;
					$left = 40;
					require_once 'dragobject.php';
					getDragObjectCoords('minion', $top, $left);
					$smarty->assign('top', $top);
					$smarty->assign('left', $left);
					$smarty->assign('minion', $smarty->fetch('minion.tpl'));
				}
				
				if (defined('DEBUG') AND (LoginLid::instance()->hasPermission('P_ADMIN') OR LoginLid::instance()->isSued())) {
					$smarty->assign('debug', SimpleHTML::getDebug());
				}
				
				if ($this->zijkolom !== false || Instelling::get('layout_beeld') === 'breedbeeld') {
					if (is_array($this->zijkolom)) {
						$this->zijkolom += SimpleHTML::getStandaardZijkolom();
					}
					else {
						$this->zijkolom = SimpleHTML::getStandaardZijkolom();
					}
				}
				$smarty->assign('zijkolom', $this->zijkolom);
				
				require_once('menu/MenuView.class.php');
				$smarty->assign('mainmenu', new MenuView('main'));
				
				$smarty->display('csrdelft.tpl');
			return;
		}
		
		// als er een error is geweest, die unsetten...
		if (isset($_SESSION['auth_error'])) {
			unset($_SESSION['auth_error']);
		}
	}
}
