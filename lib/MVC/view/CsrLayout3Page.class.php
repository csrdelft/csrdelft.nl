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
		parent::__construct($body);
		$this->titel = $body->getTitel();

		$css = '/layout3/css/';
		$js = '/layout3/js/';
		$plugin = '/layout/js/jquery/plugins/';

		if (DEBUG AND LoginModel::mag('P_ADMIN')) {
			$this->addStylesheet($css . 'bootstrap.css');
			$this->addStylesheet($css . 'bootstrap-theme.css');

			$this->addScript($js . 'jquery.js');
			$this->addScript($js . 'bootstrap.js');
			$this->addScript($js . 'bootstrap-typeahead.js');
			$this->addScript($plugin . 'jquery.autosize.js');
			$this->addScript($plugin . 'jquery.hoverIntent.js');
			$this->addScript($plugin . 'jquery.scrollTo.js');
		} else { // minimized javascript
			$this->addStylesheet($css . 'bootstrap.min.css');
			$this->addStylesheet($css . 'bootstrap-theme.min.css');

			$this->addScript($js . 'jquery.min.js');
			$this->addScript($js . 'bootstrap.min.js');
			$this->addScript($js . 'bootstrap-typeahead.min.js');
			$this->addScript($plugin . 'jquery.autosize.min.js');
			$this->addScript($plugin . 'jquery.hoverIntent.min.js');
			$this->addScript($plugin . 'jquery.scrollTo.min.js');
		}
		$this->addStylesheet($css . 'bootstrap-typeahead.css');
		$this->addStylesheet($css . 'csrdelft.css');

		$this->addScript($plugin . 'jquery.timeago.js');
		$this->addScript($js . 'csrdelft.js');

		if (LidInstellingen::get('algemeen', 'sneltoetsen') == 'ja') {
			$this->addScript('/layout/js/sneltoetsen.js');
		}
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$path = 'csrdelft3/';

		$this->smarty->display($path . 'pagina_layout.tpl');
	}

}
