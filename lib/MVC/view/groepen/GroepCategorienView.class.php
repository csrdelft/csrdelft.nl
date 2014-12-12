<?php

/**
 * GroepCategorienView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepCategorienView extends SmartyTemplateView {

	public function __construct($categorien) {
		parent::__construct(array(), 'Groep-categorien');
		foreach ($categorien as $categorie) {
			$this->model[] = new GroepCategorieView($categorie);
		}
	}

	public function view() {
		$this->smarty->display('groepen/menu_pagina.tpl');
		foreach ($this->model as $categorieView) {
			$categorieView->view();
		}
	}

}

class GroepCategorieView extends SmartyTemplateView {

	public function __construct(GroepCategorie $categorie) {
		parent::__construct($categorie);
	}

	public function view() {
		$this->smarty->assign('categorie', $this->model);
		$this->smarty->display('groepen/categorie.tpl');
	}

}
