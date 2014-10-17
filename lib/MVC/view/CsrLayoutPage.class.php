<?php

require_once 'MVC/view/Zijbalk.static.php';
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
	 * Zijbalk
	 * @var View[]
	 */
	public $zijbalk;
	/**
	 * modal inhoud
	 * @var View
	 */
	public $modal;

	public function __construct(View $body, array $zijbalk = array(), $modal = null) {
		parent::__construct($body, $body->getTitel());
		$this->zijbalk = $zijbalk;
		$this->modal = $modal;

//		$css = '/layout/css/';
//		$js = '/layout/js/';
//		$plugin = $js . 'jquery/plugins/';
//
//		$this->addStylesheet($css . 'reset');
//		$this->addStylesheet($css . 'layout_pagina');
//		$this->addStylesheet($css . 'ubb');
//		$this->addStylesheet($css . 'csrdelft');
//		$layout = LidInstellingen::get('layout', 'opmaak');
//		$this->addStylesheet($css . $layout);
//		if (LidInstellingen::get('layout', 'toegankelijk') == 'bredere letters') {
//			$this->addStylesheet($css . 'toegankelijk_bredere_letters');
//		}
//		if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
//			if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
//				$this->addStylesheet($css . 'snow.anim');
//			} else {
//				$this->addStylesheet($css . 'snow');
//			}
//		}
//		$this->addScript($js . 'jquery/jquery');
//		$this->addScript($js . 'jquery/jquery-ui');
//		$this->addStylesheet($js . 'jquery/jquery-ui');
//		$this->addScript($js . 'autocomplete/jquery.autocomplete');
//		$this->addStylesheet($js . 'autocomplete/jquery.autocomplete');
//		//$this->addScript($plugin . 'jquery.dataTables');
//		//$this->addStylesheet($css . 'jquery.dataTables');
//		$this->addScript($plugin . 'jquery.autosize');
//		$this->addScript($plugin . 'jquery.hoverIntent');
//		$this->addScript($plugin . 'jquery.scrollTo');
//		$this->addScript($plugin . 'jquery.timeago');
//		$this->addScript($js . 'csrdelft');
//		//$this->addScript($js . 'csrdelft.dataTables');
//		//$this->addStylesheet($css . 'csrdelft.dataTables');
//		$this->addScript($js . 'dragobject');
//		$this->addScript($js . 'main_menu');
//		$this->addScript($js . 'groepen');
//		if (LidInstellingen::get('layout', 'minion') == 'ja') {
//			$this->addScript($js . 'minion');
//			$this->addStylesheet($css . 'minion');
//		}
		$this->addStylesheet($this->getCompressedStyleUrl('layout', 'general'), true);
		$this->addScript($this->getCompressedScriptUrl('layout', 'general'), true);

	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('modal', $this->modal);
		$smarty->assign('body', $this->getBody());

		if ($this->zijbalk !== false) {
			if (!is_array($this->zijbalk)) {
				$this->zijbalk = array();
			}
			$this->zijbalk = Zijbalk::addStandaardZijbalk($this->zijbalk);
			if (LidInstellingen::get('zijbalk', 'scrollen') == 'apart scrollen') {
				$top = 0;
				$left = 0;
				DragObjectModel::getCoords('zijbalk', $top, $left);
				$smarty->assign('scrollfix', $top);
			}
		}
		$smarty->assign('zijbalk', $this->zijbalk);

		if (DEBUG AND ( LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued())) {
			$smarty->assign('debug', getDebug());
		}

		if (LoginModel::mag('P_LEDEN_MOD')) {
			require_once 'MVC/model/ForumModel.class.php';
			$smarty->assign('forumcount', ForumPostsModel::instance()->getAantalWachtOpGoedkeuring());

			require_once 'savedquery.class.php';
			$smarty->assign('queues', array(
				'mededelingen' => new SavedQuery(62)
			));
		}

		$top = 175;
		$left = 200;
		DragObjectModel::getCoords('modal', $top, $left);
		$smarty->assign('modaltop', $top);
		$smarty->assign('modalleft', $left);
		$top = 175;
		$left = 10;
		DragObjectModel::getCoords('bbcodepopup', $top, $left);
		$smarty->assign('ubbtop', $top);
		$smarty->assign('ubbleft', $left);

		if (LidInstellingen::get('layout', 'minion') == 'ja') {
			$top = 40;
			$left = 40;
			DragObjectModel::getCoords('minion', $top, $left);
			$smarty->assign('miniontop', $top);
			$smarty->assign('minionleft', $left);
			$smarty->assign('minion', $smarty->fetch('minion.tpl'));
		}

		//$dataTable = new DataTable('Example', 3, true);
		//$dataTable->setDataSource('example-data-2.json');
		//$smarty->assign('datatable', $dataTable)

		if (LoginModel::instance()->isPauper()) {
			$smarty->assign('menutree', MenuModel::instance()->getMenu('main'));
			$smarty->assign('loginform', new LoginForm());
			$smarty->display('MVC/layout/pauper.tpl');
		} else {
			$smarty->assign('mainmenu', new MainMenuView());
			// uitzondering voor wiki (geen main table)
			if ($this->body instanceof WikiView) {
				$smarty->display('MVC/layout/wiki.tpl');
			} else {
				$smarty->display('MVC/layout/pagina.tpl');
			}
		}
	}

}
