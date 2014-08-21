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
		parent::__construct($body);
		$this->titel = $body->getTitel();
		$this->tmpl = $template;
		$this->menutmpl = $menu;

		$layout2 = '/layout2/css/';
		$this->addStylesheet($layout2 . 'style.css');
		$this->addStylesheet($layout2 . 'foundation.css');
		$this->addStylesheet($layout2 . 'normalize.css');
		$this->addStylesheet('/layout/css/ubb.css');

		$layout = '/layout/js/';
		$this->addScript('/layout2/js/jquery.js');
		$this->addScript('/layout2/js/jquery.backstretch.js');
		$this->addScript($layout . 'jquery/plugins/jquery.timeago.js');
		$this->addScript($layout . 'jquery/plugins/jquery.hoverIntent.min.js');
		$this->addScript('/layout2/js/init.js');
		$this->addScript($layout . 'csrdelft.js');
		$this->addScript($layout . 'dragobject.js');
	}

	function view() {
		header('Content-Type: text/html; charset=UTF-8');

		if ($this->menutmpl !== '') {
			$this->smarty->assign('menutpl', $this->menutmpl);
		}
		$this->smarty->assign('body', $this->model);
		$this->smarty->assign('loginform', new LoginForm());
		$top = 180;
		$left = 10;
		DragObjectModel::getCoords('ubbhulpverhaal', $top, $left);
		$this->smarty->assign('ubbtop', $top);
		$this->smarty->assign('ubbleft', $left);

		if (LoginModel::instance()->isPauper()) {
			$this->smarty->assign('menutree', MenuModel::instance()->getMenuTree('main'));
			$this->smarty->display('MVC/layout/pauper.tpl');
		} else {
			$this->smarty->display('csrdelft2/' . $this->tmpl . '.tpl');
		}
	}

}
