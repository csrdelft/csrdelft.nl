<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# csrdelft.class.php
# -------------------------------------------------------------------
# csrdelft is de klasse waarbinnen een pagina in elkaar wordt gezet
# -------------------------------------------------------------------

class csrdelft extends HtmlPage {

	public $body;
	public $layout;
	/**
	 * De normale layout heeft een array van SimpleHTML als zijkolom
	 */
	public $zijkolom = array();
	public $popup;

	function __construct(View $body, $layout = 'normaal') {
		parent::__construct();
		$this->body = $body;
		$this->layout = $layout;

		switch ($this->layout) {

			case 'csrdelft2':
				$this->addStylesheet('style.css', '/layout2/');
				$this->addStylesheet('foundation.css', '/layout2/');
				$this->addStylesheet('normalize.css', '/layout2/');
				$this->addStylesheet('ubb.css', '/layout/');
				$this->addScript('jquery.js', '/layout2/');
				$this->addScript('jquery.backstretch.js', '/layout2/');
				$this->addScript('jquery/plugins/jquery.timeago-1.3.0.custom.js', '/layout/');
				$this->addScript('init.js', '/layout2/');
				$this->addScript('csrdelft.js', '/layout/');
				$this->addScript('dragobject.js');
				return;

			case 'normaal':
			case 'owee':
			case 'lustrum':
			default:

				$this->addStylesheet('undohtml.css');
				$this->addStylesheet('ubb.css');
				$this->addStylesheet('csrdelft.css');
				$layout = LidInstellingen::get('layout', 'layout');
				$this->addStylesheet($layout . '.css');
				if (LidInstellingen::get('layout', 'beeld') == 'breedbeeld') {
					$this->addStylesheet('breedbeeld.css');
				}
				if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
					if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
						$this->addStylesheet('snow.anim.css');
					} else {
						$this->addStylesheet('snow.css');
					}
				}
				if (defined('DEBUG') AND (LoginLid::instance()->hasPermission('P_ADMIN') OR LoginLid::instance()->isSued())) {
					$this->addStylesheet('jquery-ui.css', '/layout/js/jquery/themes/ui-lightness/');
					$this->addScript('jquery/jquery-2.1.0.js');
					$this->addScript('jquery/jquery-ui-1.10.4.custom.js');
				} else { // minimized javascript
					$this->addStylesheet('jquery-ui.min.css', '/layout/js/jquery/themes/ui-lightness/');
					$this->addScript('jquery/jquery-2.1.0.min.js');
					$this->addScript('jquery/jquery-ui-1.10.4.custom.min.js');
				}
				$this->addScript('jquery/plugins/jquery.timeago-1.3.0.custom.js');
				$this->addScript('jquery/plugins/jquery.hoverIntent-r7.min.js');
				$this->addScript('csrdelft.js');
				$this->addScript('dragobject.js');
				$this->addScript('menu.js');

				if (LidInstellingen::get('algemeen', 'sneltoetsen') == 'ja') {
					$this->addScript('sneltoetsen.js');
				}
				return;
		}
	}

	/**
	 * Voor layout csrdelft2 zijn extra parameters nodig:
	 * @param string $template
	 * @param string $menutemplate
	 */
	function view($template = '', $menutemplate = '') {

		header('Content-Type: text/html; charset=UTF-8');
		$this->smarty->assign('body', $this->body);

		switch ($this->layout) {

			case 'csrdelft2':

				if ($template === '') {
					$template = 'content';
				}
				if ($menutemplate !== '') {
					$this->smarty->assign('menutpl', $menutemplate);
				}
				$this->smarty->display('csrdelft2/' . $template . '.tpl');
				break;

			case 'normaal':
			case 'owee':
			case 'lustrum':
			default:

				if (LidInstellingen::get('layout', 'minion') == 'ja') {
					$this->addStylesheet('minion.css');
					$this->addScript('minion.js');
					$top = 40;
					$left = 40;
					require_once 'dragobject.class.php';
					DragObject::getCoords('minion', $top, $left);
					$this->smarty->assign('top', $top);
					$this->smarty->assign('left', $left);
					$this->smarty->assign('minion', $this->smarty->fetch('minion.tpl'));
				}

				if (defined('DEBUG') AND (LoginLid::instance()->hasPermission('P_ADMIN') OR LoginLid::instance()->isSued())) {
					$this->smarty->assign('debug', SimpleHTML::getDebug());
				}

				if ($this->zijkolom !== false || LidInstellingen::get('layout', 'beeld') === 'breedbeeld') {
					if (is_array($this->zijkolom)) {
						$this->zijkolom = array_merge($this->zijkolom, SimpleHTML::getStandaardZijkolom());
					} else {
						$this->zijkolom = SimpleHTML::getStandaardZijkolom();
					}
				}
				$this->smarty->assign('zijkolom', $this->zijkolom);
				$this->smarty->assign('popup', $this->popup);

				require_once('MVC/view/MenuView.class.php');
				$this->smarty->assign('mainmenu', new MenuView('main', 0));

				$this->smarty->display('csrdelft.tpl');
				break;
		}

		// als er een error is geweest, die unsetten...
		if (isset($_SESSION['auth_error'])) {
			unset($_SESSION['auth_error']);
		}
	}

}
