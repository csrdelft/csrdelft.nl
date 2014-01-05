<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrdelft.class.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezooid
# -------------------------------------------------------------------

require_once('menu/menu.class.php');

class csrdelft extends SimpleHTML {

	private $_body;
	private $_mainmenu;
	private $_zijkolom = array();
	private $_stylesheets = array();
	private $_scripts = array();

	function __construct(SimpleHTML $body) {
		$this->_body = $body;
		$this->_mainmenu = new Menu('main');

		$this->addStylesheet('undohtml.css');
		$this->addStylesheet('ubb.css');
		$this->addStylesheet(Instelling::get('layout') . '.css');
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
		$this->addScript('jquery.js');
		$this->addScript('jquery.timeago.js');
		$this->addScript('csrdelft.js');
		$this->addScript('dragobject.js');
		$this->addScript('menu.js');
		if (Instelling::get('algemeen_sneltoetsen') == 'ja') {
			$this->addScript('sneltoetsen.js');
		}
		if (Instelling::get('layout_minion') == 'ja') {
			$this->addScript('minion.js');
			$this->addStylesheet('minion.css');
		}
	}

	function setZijkolom($zijkolom) {
		if (is_bool($zijkolom) || is_array($zijkolom)) {
			$this->_zijkolom = $zijkolom;
		}
		else {
			throw new \Exception('Zijkolom ongeldig');
		}
	}

	function addZijkolom(SimpleHTML $block) {
		$this->_zijkolom[] = $block;
	}

	function insertZijkolom(SimpleHTML $block, $index) {
		array_splice($this->_zijkolom, $index, 0, $block);
	}

	function getTitel() {
		return 'C.S.R. Delft | ' . mb_htmlentities($this->_body->getTitel());
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
	 * Merk op: local-entry wordt ook gebruikt om een map buiten /layout/ toe te voegen.
	 */
	function addStylesheet($sheet, $localpath = '/layout/') {
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
	 * Merk op: local-entry wordt ook gebruikt om een map buiten /layout/ toe te voegen.
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

	public function getDebug($sql = true, $get = true, $post = true, $files = false, $session = true, $cookie = true) {
		$debug = '';
		if ($sql) {
			$debug .= '<hr />SQL<hr />';
			$debug .= '<pre>' . htmlentities(print_r(array("PDO" => CsrPdo::instance()->getQueries(), "MySql" => MySql::instance()->getQueries()), true)) . '</pre>';
		}
		if ($get) {
			$debug .= '<hr />GET<hr />';
			if (count($_GET) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_GET, true)) . '</pre>';
			}
		}
		if ($post) {
			$debug .= '<hr />POST<hr />';
			if (count($_POST) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_POST, true)) . '</pre>';
			}
		}
		if ($files) {
			$debug .= '<hr />FILES<hr />';
			if (count($_FILES) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_FILES, true)) . '</pre>';
			}
		}
		if (isset($_GET['debug_session'])) { // only print session if relevent, because it might be quite big.
			$debug .= '<hr />SESSION<hr />';
			if (count($_SESSION) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_SESSION, true)) . '</pre>';
			}
		}
		if ($cookie) {
			$debug .= '<hr />COOKIE<hr />';
			if (count($_COOKIE) > 0) {
				$debug .= '<pre>' . htmlentities(print_r($_COOKIE, true)) . '</pre>';
			}
		}
		return $debug;
	}

	private function standaardZijkolom() {
		// Is het al...
		if (Instelling::get('zijbalk_ishetal') != 'niet weergeven') {
			$this->addZijkolom(new IsHetAlContent(Instelling::get('zijbalk_ishetal')));
		}
		// Ga snel naar
		if (Instelling::get('zijbalk_gasnelnaar') == 'ja') {
			$this->addZijkolom(new Menu('gasnelnaar', 3));
		}
		// Agenda
		if (LoginLid::instance()->hasPermission('P_AGENDA_READ') && Instelling::get('zijbalk_agendaweken') > 0) {
			require_once('agenda/agenda.class.php');
			require_once('agenda/agendacontent.class.php');
			$this->addZijkolom(new AgendaZijbalkContent(new Agenda(), Instelling::get('zijbalk_agendaweken')));
		}
		// Laatste mededelingen
		if (Instelling::get('zijbalk_mededelingen') > 0) {
			require_once('mededelingen/mededeling.class.php');
			require_once('mededelingen/mededelingencontent.class.php');
			$this->addZijkolom(new MededelingenZijbalkContent(Instelling::get('zijbalk_mededelingen')));
		}
		// Nieuwste belangrijke forumberichten
		if (Instelling::get('zijbalk_forum_belangrijk') >= 0) {
			require_once 'forum/forumcontent.class.php';
			$this->addZijkolom(new ForumContent('lastposts_belangrijk'));
		}
		// Nieuwste forumberichten
		if (Instelling::get('zijbalk_forum') > 0) {
			require_once 'forum/forumcontent.class.php';
			$this->addZijkolom(new ForumContent('lastposts'));
		}
		// Zelfgeposte forumberichten
		if (Instelling::get('zijbalk_forum_zelf') > 0) {
			require_once 'forum/forumcontent.class.php';
			$this->addZijkolom(new ForumContent('lastposts_zelf'));
		}
		// Nieuwste fotoalbum
		if (Instelling::get('zijbalk_fotoalbum') == 'ja') {
			require_once 'fotoalbumcontent.class.php';
			$this->addZijkolom(new FotalbumZijbalkContent());
		}
		// Komende verjaardagen
		if (Instelling::get('zijbalk_verjaardagen') > 0) {
			require_once 'lid/verjaardagcontent.class.php';
			$this->addZijkolom(new VerjaardagContent('komende'));
		}
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');
		$smarty = new Smarty_csr();
		$smarty->assign_by_ref('this', $this);

		if (Instelling::get('layout_minion') == 'ja') {
			$smarty->assign('minion', $smarty->fetch('minion.tpl'));
		}

		if ($this->_zijkolom !== false || Instelling::get('layout_beeld') === 'breedbeeld') {
			$this->standaardZijkolom();
		}
		$smarty->assign('zijkolom', $this->_zijkolom);

		if (defined('DEBUG') AND (LoginLid::instance()->hasPermission('P_ADMIN') OR LoginLid::instance()->isSued())) {
			$smarty->assign('db', MySql::instance());
		}

		$smarty->assign('mainmenu', $this->_mainmenu);
		$smarty->assign('body', $this->_body);
		$smarty->display('csrdelft.tpl');

		// als er een error is geweest, die unsetten...
		if (isset($_SESSION['auth_error'])) {
			unset($_SESSION['auth_error']);
		}
	}

}
