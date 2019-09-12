<?php

namespace CsrDelft\view;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CsrLayoutExternPage extends CompressedLayout {

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

		view('layout-extern.' . $this->tmpl, [
			'titel' => $this->getTitel(),
			'body' => $this->getBody(),
			'showmenu' => $this->showMenu,
		])->view();
	}
}
