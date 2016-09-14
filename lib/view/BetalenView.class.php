<?php

/**
 * BetalenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BetalenView extends SmartyTemplateView {

	public function view() {
		$this->smarty->display('betalen/react_example.tpl');
		echo '<script type="text/babel" src="/layout/jsx/react_example.js"></script>';
	}

}
