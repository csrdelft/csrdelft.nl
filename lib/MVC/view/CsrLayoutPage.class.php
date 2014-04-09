<?php

require_once 'MVC/view/HtmlPage.abstract.php';
require_once 'MVC/view/MenuView.class.php';
require_once 'MVC/model/MenuModel.class.php';
require_once 'MVC/model/DragObjectModel.class.php';

/**
 * CsrLayoutPage.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2006
 */
class CsrLayoutPage extends HtmlPage {

	/**
	 * Zijkolom
	 * @var SimpleHTML[]
	 */
	public $zijkolom;
	/**
	 * Popup inhoud
	 * @var View
	 */
	public $popup;

	public function __construct(View $body, array $zijkolom = array(), $popup = null) {
		parent::__construct($body);
		$this->zijkolom = $zijkolom;
		$this->popup = $popup;

		$this->addStylesheet('undohtml.css');
		$this->addStylesheet('ubb.css');
		$this->addStylesheet('csrdelft.css');
		$layout = LidInstellingen::get('layout', 'layout');
		$this->addStylesheet($layout . '.css');
		if (LidInstellingen::get('layout', 'beeld') == 'breedbeeld') {
			$this->addStylesheet('breedbeeld.css');
		}
		if (LidInstellingen::get('layout', 'fixed') == 'vast') {
			$this->addStylesheet('fixed.css');
		}
		if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
			if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
				$this->addStylesheet('snow.anim.css');
			} else {
				$this->addStylesheet('snow.css');
			}
		}
		if (defined('DEBUG') AND (LoginLid::mag('P_ADMIN') OR LoginLid::instance()->isSued())) {
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
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$this->addStylesheet('minion.css');
			$this->addScript('minion.js');
			$top = 40;
			$left = 40;
			DragObjectModel::getCoords('minion', $top, $left);
			$this->smarty->assign('miniontop', $top);
			$this->smarty->assign('minionleft', $left);
			$this->smarty->assign('minion', $this->smarty->fetch('minion.tpl'));
		}

		if (defined('DEBUG') AND (LoginLid::mag('P_ADMIN') OR LoginLid::instance()->isSued())) {
			$this->smarty->assign('debug', SimpleHTML::getDebug());
		}

		if ($this->zijkolom !== false || LidInstellingen::get('layout', 'beeld') === 'breedbeeld') {
			if (is_array($this->zijkolom)) {
				$this->zijkolom = array_merge($this->zijkolom, SimpleHTML::getStandaardZijkolom());
			} else {
				$this->zijkolom = SimpleHTML::getStandaardZijkolom();
			}
		}

		$this->smarty->assign('mainmenu', new MainMenuView(MenuModel::instance()->getMenuTree('main')));
		$this->smarty->assign('body', $this->model);
		$this->smarty->assign('zijkolom', $this->zijkolom);
		$this->smarty->assign('popup', $this->popup);

		$top = 180;
		$left = 190;
		DragObjectModel::getCoords('popup', $top, $left);
		$this->smarty->assign('popuptop', $top);
		$this->smarty->assign('popupleft', $left);
		$top = 180;
		$left = 10;
		DragObjectModel::getCoords('ubbhulpverhaal', $top, $left);
		$this->smarty->assign('ubbtop', $top);
		$this->smarty->assign('ubbleft', $left);

		if (array_key_exists('pauper', $_SESSION)) {
			$this->smarty->assign('menutree', MenuModel::instance()->getMenuTree('main'));
			$this->smarty->display('MVC/layout/pauper.tpl');
		} else {
			$this->smarty->display('csrdelft.tpl');
		}
	}

}
