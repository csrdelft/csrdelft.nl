<?php

class WikiHeader extends HtmlPage {

	public function __construct() {
		parent::__construct(new MainMenuView(), null);

		$css = '/layout/css/';
		$js = '/layout/js/';
		//$plugin = $js . 'jquery/plugins/';

		$this->addStylesheet($css . 'reset_nav');
		$this->addStylesheet($css . 'layout_pagina');
		//$this->addStylesheet($css . 'ubb');
		//$this->addStylesheet($css . 'csrdelft');
		$layout = LidInstellingen::get('layout', 'opmaak');
		$this->addStylesheet($css . $layout);
		if (LidInstellingen::get('layout', 'toegankelijk') == 'bredere letters') {
			$this->addStylesheet($css . 'toegankelijk_bredere_letters');
		}
		if (LidInstellingen::get('layout', 'sneeuw') != 'nee') {
			if (LidInstellingen::get('layout', 'sneeuw') == 'ja') {
				$this->addStylesheet($css . 'snow.anim');
			} else {
				$this->addStylesheet($css . 'snow');
			}
		}
		$this->addScript($js . 'jquery/jquery');
		//$this->addScript($js . 'jquery/jquery-ui');
		//$this->addStylesheet($js . 'jquery/jquery-ui');
		$this->addScript($js . 'autocomplete/jquery.autocomplete');
		$this->addStylesheet($js . 'autocomplete/jquery.autocomplete');

		$this->addScript('/layout/js/main_menu');
	}

	public function view() {
		foreach ($this->getStylesheets() as $sheet) {
			echo '<link rel="stylesheet" href="' . $sheet . '" type="text/css" />';
		}
		foreach ($this->getScripts() as $script) {
			echo '<script type="text/javascript" src="' . $script . '"></script>';
		}
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