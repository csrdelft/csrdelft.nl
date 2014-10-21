<?php

require_once 'MVC/view/LoginView.class.php';

/**
 * CsrLayout2Page.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout van 2013
 */
class CsrLayout2Page extends HtmlPage {

	/**
	 * Content template
	 * @var string
	 */
	public $tmpl;
	/**
	 * Menu template
	 * @var string
	 */
	public $menutmpl;

	function __construct(View $body, $template = 'content', $menu = '') {
		parent::__construct($body, $body->getTitel());
		$this->tmpl = $template;
		$this->menutmpl = $menu;

		$this->addStylesheet($this->getCompressedStyleUrl('layout2', 'general'), true);
		$this->addScript($this->getCompressedScriptUrl('layout2', 'general'), true);
	}

	public function getBreadcrumbs() {
		return null;
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());

		if ($this->menutmpl !== '') {
			$smarty->assign('menutpl', $this->menutmpl);
		}
		$smarty->assign('body', $this->getBody());

		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if (!$breadcrumbs) {
			$breadcrumbs = $this->getBreadcrumbs();
		}
		$smarty->assign('breadcrumbs', $breadcrumbs);

		$smarty->assign('loginform', new LoginForm());
		$top = 180;
		$left = 10;
		DragObjectModel::getCoords('bbcodehulp', $top, $left);
		$smarty->assign('bbhulptop', $top);
		$smarty->assign('bbhulpleft', $left);

		if (LoginModel::instance()->isPauper()) {
			$smarty->assign('menutree', MenuModel::instance()->getMenu('main'));
			$smarty->display('MVC/layout/pauper.tpl');
		} else {
			$smarty->display('csrdelft2/' . $this->tmpl . '.tpl');
		}
	}

}
