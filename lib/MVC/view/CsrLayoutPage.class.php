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
 * De stek layout van 2006
 */
class CsrLayoutPage extends HtmlPage {

	/**
	 * Zijkolom SimpleHTML
	 * @var array
	 */
	public $zijkolom;
	/**
	 * Popup inhoud
	 * @var View
	 */
	public $popup;

	public function __construct(View $body, array $zijkolom = array(), $popup = null) {
		parent::__construct($body, $body->getTitel());
		$this->zijkolom = $zijkolom;
		$this->popup = $popup;

		$css = '/layout/css/';
		$js = '/layout/js/';
		$plugin = $js . 'jquery/plugins/';

		$this->addStylesheet($css . 'undohtml');
		$this->addStylesheet($css . 'ubb');
		$this->addStylesheet($css . 'csrdelft');
		$layout = LidInstellingen::get('layout', 'layout');
		$this->addStylesheet($css . $layout);
		if (LidInstellingen::get('layout', 'beeld') == 'breedbeeld') {
			$this->addStylesheet($css . 'breedbeeld');
		}
		if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
			if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
				$this->addStylesheet($css . 'snow.anim');
			} else {
				$this->addStylesheet($css . 'snow');
			}
		}
		$this->addScript($js . 'jquery/jquery');
		$this->addScript($js . 'jquery/jquery-ui');
		$this->addStylesheet($js . 'jquery/jquery-ui');
		$this->addScript($js . 'autocomplete/jquery.autocomplete');
		$this->addStylesheet($js . 'autocomplete/jquery.autocomplete');
		//$this->addScript($plugin . 'jquery.dataTables');
		//$this->addStylesheet($css . 'jquery.dataTables');
		$this->addScript($plugin . 'jquery.autosize');
		$this->addScript($plugin . 'jquery.hoverIntent');
		$this->addScript($plugin . 'jquery.scrollTo');
		$this->addScript($plugin . 'jquery.timeago');
		$this->addScript($js . 'csrdelft');
		//$this->addScript($js . 'csrdelft.dataTables');
		//$this->addStylesheet($css . 'csrdelft.dataTables');
		$this->addScript($js . 'dragobject');
		$this->addScript($js . 'menu');
		$this->addScript($js . 'groepen');
		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$this->addScript($js . 'minion');
			$this->addStylesheet($css . 'minion');
		}
		if (LidInstellingen::get('algemeen', 'sneltoetsen') == 'ja') {
			$this->addScript($js . 'sneltoetsen');
		}
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());

		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$top = 40;
			$left = 40;
			DragObjectModel::getCoords('minion', $top, $left);
			$smarty->assign('miniontop', $top);
			$smarty->assign('minionleft', $left);
			$smarty->assign('minion', $smarty->fetch('minion.tpl'));
		}

		if (DEBUG AND ( LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())) {
			$smarty->assign('debug', SimpleHTML::getDebug());
		}

		if ($this->zijkolom !== false || LidInstellingen::get('layout', 'beeld') === 'breedbeeld') {
			if (is_array($this->zijkolom)) {
				$this->zijkolom = array_merge($this->zijkolom, SimpleHTML::getStandaardZijkolom());
			} else {
				$this->zijkolom = SimpleHTML::getStandaardZijkolom();
			}
		}

		$smarty->assign('mainmenu', new MainMenuView(MenuModel::instance()->getMenuTree('main')));
		$smarty->assign('body', $this->getBody());
		$smarty->assign('zijkolom', $this->zijkolom);
		$smarty->assign('popup', $this->popup);
		//$smarty->assign('datatable', new DataTable('Example', '/onderhoud.html?name=', 3));

		$top = 180;
		$left = 190;
		DragObjectModel::getCoords('popup', $top, $left);
		$smarty->assign('popuptop', $top);
		$smarty->assign('popupleft', $left);
		$top = 180;
		$left = 10;
		DragObjectModel::getCoords('ubbhulpverhaal', $top, $left);
		$smarty->assign('ubbtop', $top);
		$smarty->assign('ubbleft', $left);

		if (LoginModel::instance()->isPauper()) {
			$smarty->assign('menutree', MenuModel::instance()->getMenuTree('main'));
			$smarty->display('MVC/layout/pauper.tpl');
		} else {
			$smarty->display('csrdelft.tpl');
		}
	}

}
