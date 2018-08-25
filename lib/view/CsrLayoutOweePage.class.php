<?php

namespace CsrDelft\view;

use CsrDelft\view\renderer\BladeRenderer;


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
	public $showMenu;

	function __construct(View $body, $template = 'content', $menu = false) {
		parent::__construct($body, $body->getTitel());
		$this->tmpl = $template;
		$this->showMenu = $menu;
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

		$renderer = new BladeRenderer('layout-owee.' . $this->tmpl);
		$renderer->assign('stylesheets', $this->getStylesheets());
		$renderer->assign('scripts', $this->getScripts());
		$renderer->assign('titel', $this->getTitel());
		$renderer->assign('body', $this->getBody());
		$renderer->assign('showmenu', $this->showMenu);

		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if (!$breadcrumbs) {
			$breadcrumbs = $this->getBreadcrumbs();
		}
		$renderer->assign('breadcrumbs', $breadcrumbs);

		$renderer->display();
	}

}
