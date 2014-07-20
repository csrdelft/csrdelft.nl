<?php

/**
 * CsrLayout2Page.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2013
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
		parent::__construct($body);
		$this->tmpl = $template;
		$this->menutmpl = $menu;

		$this->addStylesheet('style.css', '/layout2/');
		$this->addStylesheet('foundation.css', '/layout2/');
		$this->addStylesheet('normalize.css', '/layout2/');
		$this->addStylesheet('ubb.css', '/layout/');
		$this->addScript('jquery.js', '/layout2/');
		$this->addScript('jquery.backstretch.js', '/layout2/');
		$this->addScript('jquery/plugins/jquery.timeago-1.3.0.custom.js', '/layout/');
		$this->addScript('jquery/plugins/jquery.hoverIntent.min.js');
		$this->addScript('init.js', '/layout2/');
		$this->addScript('csrdelft.js', '/layout/');
		$this->addScript('dragobject.js', '/layout/');
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		if ($this->menutmpl !== '') {
			$this->smarty->assign('menutpl', $this->menutmpl);
		}
		$this->smarty->assign('body', $this->model);
		$top = 180;
		$left = 10;
		DragObjectModel::getCoords('ubbhulpverhaal', $top, $left);
		$this->smarty->assign('ubbtop', $top);
		$this->smarty->assign('ubbleft', $left);

		if (isset($_SESSION['pauper'])) {
			$this->smarty->assign('menutree', MenuModel::instance()->getMenuTree('main'));
			$this->smarty->display('MVC/layout/pauper.tpl');
		} else {
			$this->smarty->display('csrdelft2/' . $this->tmpl . '.tpl');
		}
	}

}
