<?php

namespace CsrDelft\view;

use CsrDelft\view\login\LoginForm;


/**
 * CsrLayoutOweePage.class.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Externe layout voor Owee 2016
 */
class CsrLayoutOweePage extends HtmlPage {

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
		$this->addScript('/dist/js/manifest.js');
		$this->addScript('/dist/js/extern-vendor.js');
		$this->addScript('/dist/js/extern.js');
		$this->addStylesheet('/dist/css/extern.css');
		$this->addStylesheet('/dist/css/extern-forum.css');
		$this->addStylesheet('/dist/css/extern-fotoalbum.css');
	}

	public function getBreadcrumbs() {
		return null;
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
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
