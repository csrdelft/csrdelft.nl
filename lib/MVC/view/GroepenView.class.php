<?php

require_once 'MVC/model/entity/groepen/GroepTab.enum.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenView extends TemplateView {

	public function __construct($groepen) {
		parent::__construct();
		$this->model = array();
		foreach ($groepen as $groep) {
			$this->model[] = new GroepView($groep);
		}
	}

	public function view() {
		$this->smarty->display('MVC/groepen/menu_pagina.tpl');
		foreach ($this->model as $groepView) {
			$groepView->view();
		}
	}

}

class GroepCategorienView extends TemplateView {

	public function __construct($categorien) {
		parent::__construct($categorien);
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

class GroepView extends TemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		$this->smarty->assign('groep', $groep);
		$this->smarty->assign('link', '/groepen/' . strtolower(get_class($groep)));
	}

	public function view() {
		$this->smarty->display('MVC/groepen/groep.tpl');
	}

}

class CommissiesView extends GroepenView {
	
}

class BesturenView extends GroepenView {
	
}

class SjaarciesView extends GroepenView {
	
}

class OnderverenigingenView extends GroepenView {
	
}

class WerkgroepenView extends GroepenView {
	
}

class WoonoordenView extends GroepenView {
	
}

class ActiviteitenView extends GroepenView {
	
}

class ConferentiesView extends GroepenView {
	
}

class KetzersView extends GroepenView {
	
}

class KetzerSelectorsView extends GroepenView {
	
}

class KetzerOptiesView extends GroepenView {
	
}

class KetzerKeuzesView extends GroepenView {
	
}
