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

		$this->addStylesheet($this->getCompressedStyleUrl('layout3', 'general'), true);
		$this->addScript($this->getCompressedScriptUrl('layout3', 'general'), true);
	}

	public function getBreadcrumbs() {
		return null;
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
