<?php

require_once 'MVC/model/entity/groepen/GroepTab.enum.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenView extends TemplateView {

	public function view() {
		$this->smarty->display('MVC/groepen/menu_pagina.tpl');
		foreach ($this->model as $groep) {
			$this->smarty->assign('groep', $groep);
			$this->smarty->assign('link', '/groepen/' . strtolower(get_class($groep)));
			$this->smarty->display('MVC/groepen/groep.tpl');
		}
	}

}
