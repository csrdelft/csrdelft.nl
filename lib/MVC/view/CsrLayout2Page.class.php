<?php

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
		parent::__construct($body);
		$this->titel = $body->getTitel();
		$this->tmpl = $template;
		$this->menutmpl = $menu;

		$layout = '/layout2/css/';
		$this->addStylesheet('style.css', $layout);
		$this->addStylesheet('foundation.css', $layout);
		$this->addStylesheet('normalize.css', $layout);
		$this->addStylesheet('ubb.css');

		$layout = '/layout2/js/';
		$this->addScript('jquery.js', $layout);
		$this->addScript('jquery.backstretch.js', $layout);
		$this->addScript('jquery.timeago.js', '/layout/js/jquery/plugins/');
		$this->addScript('jquery.hoverIntent.min.js', '/layout/js/jquery/plugins/');
		$this->addScript('init.js', $layout);
		$this->addScript('csrdelft.js');
		$this->addScript('dragobject.js');
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
