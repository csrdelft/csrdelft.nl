<?php

namespace CsrDelft\view;

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
	}

	public function getBreadcrumbs() {
		return null;
	}

	/**
	 * @throws \Exception
	 */
	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$breadcrumbs = $this->getBody()->getBreadcrumbs();
		if (!$breadcrumbs) {
			$breadcrumbs = $this->getBreadcrumbs();
		}

		view('layout-owee.' . $this->tmpl, [
			'titel' => $this->getTitel(),
			'body' => $this->getBody(),
			'showmenu' => $this->showMenu,
			'breadcrumbs' => $breadcrumbs
		])->view();
	}
}
