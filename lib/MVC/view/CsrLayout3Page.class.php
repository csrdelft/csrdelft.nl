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
		$plugin = '/layout/js/jquery/plugins/';

		if (DEBUG) {
			$this->addStylesheet($css . 'bootstrap.css');
			$this->addStylesheet($css . 'bootstrap-theme.css');
			$this->addStylesheet($css . 'bootstrap-typeahead.css');

			$this->addScript($js . 'jquery.js');
			$this->addScript($js . 'mustache.js');
			$this->addScript($js . 'bootstrap.js');
			$this->addScript($js . 'typeahead.bundle.js');
			$this->addScript($plugin . 'jquery.autosize.js');
			$this->addScript($plugin . 'jquery.hoverIntent.js');
			$this->addScript($plugin . 'jquery.scrollTo.js');
			$this->addScript($plugin . 'jquery.timeago.js');
		} else { // minimized
			$this->addStylesheet($css . 'bootstrap.min.css');
			$this->addStylesheet($css . 'bootstrap-theme.min.css');
			$this->addStylesheet($css . 'bootstrap-typeahead.min.css');

			$this->addScript($js . 'jquery.min.js');
			$this->addScript($js . 'mustache.min.js');
			$this->addScript($js . 'bootstrap.min.js');
			$this->addScript($js . 'typeahead.bundle.min.js');
			$this->addScript($plugin . 'jquery.autosize.min.js');
			$this->addScript($plugin . 'jquery.hoverIntent.min.js');
			$this->addScript($plugin . 'jquery.scrollTo.min.js');
			$this->addScript($plugin . 'jquery.timeago.min.js');
		}
		$this->addStylesheet($css . 'csrdelft.css');
		$this->addScript($js . 'csrdelft.js');

		if (LidInstellingen::get('algemeen', 'sneltoetsen') == 'ja') {
			$this->addScript('/layout/js/sneltoetsen.js');
		}
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$smarty = CsrSmarty::instance();
		$smarty->assign('stylesheets', $this->getStylesheets());
		$smarty->assign('scripts', $this->getScripts());
		$smarty->assign('titel', $this->getTitel());

		$smarty->assign('body', $this->body);

		$smarty->display('csrdelft3/pagina_layout.tpl');
	}

}
