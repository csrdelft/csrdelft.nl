<?php

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class CommissiesView extends TemplateView {

	public function __construct(array $commissies) {
		parent::__construct($commissies);
		$this->smarty->assign('commissies', $this->model);
	}

	public function getTitel() {
		return 'Commissies (h.t.)';
	}

	public function view() {
		$this->smarty->display('MVC/groepen/commissies.tpl');
	}

}
