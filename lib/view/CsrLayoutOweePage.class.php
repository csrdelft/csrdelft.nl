<?php

require_once 'view/LoginView.class.php';

/**
 * CsrLayoutOweePage.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Externe layout voor Owee 2016
 */
class CsrLayoutOweePage extends CompressedLayout {

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
		parent::__construct('layout-owee', $body, $body->getTitel());
		$this->tmpl = $template;
		$this->menutmpl = $menu;
		$this->addCompressedResources('general');
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
		$smarty->assign('loginform', new LoginForm());
		$smarty->assign('body', $this->getBody());

		if ($this->menutmpl !== '') {
			$smarty->assign('menutpl', $this->menutmpl);
		}
		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if (!$breadcrumbs) {
			$breadcrumbs = $this->getBreadcrumbs();
		}
		$smarty->assign('breadcrumbs', $breadcrumbs);

		$smarty->display('layout-owee/' . $this->tmpl . '.tpl');
	}

}
