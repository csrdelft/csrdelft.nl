<?php

class WikiSmarty extends SmartyTemplateView {

	public function view() {
		$this->smarty->assign('mainmenu', new MainMenuView());
	}

}

class WikiHeader extends HtmlPage {

	public function __construct() {
		parent::__construct(new WikiSmarty(null, null), null);
		$this->addStylesheet('/layout/css/layout_pagina');
		$this->addScript('/layout/js/main_menu');
	}

	public function view() {

		foreach ($this->getStylesheets() as $sheet) {
			echo '<link rel="stylesheet" href="' . $sheet . '" type="text/css" />';
		}
		foreach ($this->getScripts() as $script) {
			echo '<script type="text/javascript" src="' . $script . '"></script>';
		}
		// assign mainmenu
		$this->body->view();
	}

}

$wiki = new WikiHeader();
$wiki->view();
?>
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-19828019-4']);
	_gaq.push(['_trackPageview']);
	(function () {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0];
		s.parentNode.insertBefore(ga, s);
	})();
</script>