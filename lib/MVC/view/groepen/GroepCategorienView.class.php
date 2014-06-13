<?php

/**
 * GroepCategorienView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepCategorienView extends TemplateView {

	public function __construct($categorien) {
		parent::__construct(array(), 'Groep-categorien');
		foreach ($categorien as $categorie) {
			$this->model[] = new GroepCategorieView($categorie);
		}
	}

	public function view() {
		$this->smarty->display('MVC/groepen/menu_pagina.tpl');
		foreach ($this->model as $categorieView) {
			$categorieView->view();
		}
	}

}

class GroepCategorieView extends TemplateView {

	public function __construct(GroepCategorie $categorie) {
		parent::__construct($categorie);
		$this->smarty->assign('groep', $categorie);
	}

	public function view() {
		$this->smarty->display('MVC/groepen/categorie.tpl');
	}

}
