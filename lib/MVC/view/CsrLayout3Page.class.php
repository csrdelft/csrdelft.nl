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

		if (defined('DEBUG') AND LoginSession::mag('P_ADMIN')) {
			$this->addStylesheet('bootstrap.css', $css);
			$this->addStylesheet('bootstrap-theme.css', $css);

			$this->addScript('jquery.js', $js);
			$this->addScript('bootstrap.js', $js);
			$this->addScript('bootstrap-typeahead.js', $js);
			$this->addScript('jquery.autosize.js', $plugin);
			$this->addScript('jquery.hoverIntent.js', $plugin);
			$this->addScript('jquery.scrollTo.js', $plugin);
		} else { // minimized javascript
			$this->addStylesheet('bootstrap.min.css', $css);
			$this->addStylesheet('bootstrap-theme.min.css', $css);

			$this->addScript('jquery.min.js', $js);
			$this->addScript('bootstrap.min.js', $js);
			$this->addScript('bootstrap-typeahead.min.js', $js);
			$this->addScript('jquery.autosize.min.js', $plugin);
			$this->addScript('jquery.hoverIntent.min.js', $plugin);
			$this->addScript('jquery.scrollTo.min.js', $plugin);
		}
		$this->addStylesheet('bootstrap-typeahead.css', $css);
		$this->addStylesheet('csrdelft.css', $css);

		$this->addScript('jquery.timeago.js', $plugin);
		$this->addScript('csrdelft.js', $js);

		if (LidInstellingen::get('algemeen', 'sneltoetsen') == 'ja') {
			$this->addScript('sneltoetsen.js');
		}
	}

	public function view() {
		header('Content-Type: text/html; charset=UTF-8');

		$path = 'csrdelft3/';

		$this->smarty->display($path . 'pagina_layout.tpl');
	}

}
