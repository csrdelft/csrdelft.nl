<?php

require_once 'MVC/view/HtmlPage.abstract.php';
require_once 'MVC/view/MenuView.class.php';
require_once 'MVC/model/MenuModel.class.php';
require_once 'MVC/model/DragObjectModel.class.php';

/**
 * CsrLayout3Page.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2014
 */
class CsrLayout3Page extends HtmlPage {

	public function __construct(View $body) {
		parent::__construct($body, $body->getTitel());

		$css = '/layout3/css/';
		$js = '/layout3/js/';

		$this->addStylesheet($css . 'bootstrap');
		$this->addStylesheet($css . 'bootstrap-theme');
		$this->addStylesheet($css . 'bootstrap-typeahead');

		$this->addScript($js . 'jquery');
		$this->addScript($js . 'bootstrap');
		$this->addScript($js . 'typeahead.bundle');
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());
		$smarty->assign('body', $this->getBody());

		$smarty->display('csrdelft3/pagina_layout.tpl');
	}

}
