<?php

require_once 'MVC/model/entity/groepen/GroepTab.enum.php';
require_once 'MVC/model/CmsPaginaModel.class.php';
require_once 'MVC/view/CmsPaginaView.class.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GroepenView extends TemplateView {

	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct($groepen, $pagina, $titel = '') {
		parent::__construct($groepen, $titel);
		$this->pagina = CmsPaginaModel::instance()->getPagina($pagina);
		if ($this->pagina) {
			$this->titel = $this->pagina->titel;
		}
		$this->smarty->assign('groepen', $this->model);
	}

	public function view() {
		$this->smarty->display('MVC/groepen/menu_pagina.tpl');
		//$this->smarty->display('MVC/groepen/inhoudsopgave.tpl'); //FIXME: cannot iterate more than once over PDO statement of groepen
		if ($this->pagina) {
			$pagina = new CmsPaginaView($this->pagina);
			$pagina->view();
		}
		foreach ($this->model as $groep) {
			$class = get_class($groep) . 'View';
			$class = new $class($groep);
			$class->view();
		}
	}

}

abstract class GroepView extends TemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		$forms = array();
		foreach ($groep->getGroepLeden() as $groeplid) {
			$forms[$groeplid->lid_id] = new GroepLidForm($groeplid);
		}
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('lidforms', $forms);
	}

	public function view() {
		$this->smarty->display('MVC/groepen/groep.tpl'); //TODO: get_class($this->model)
	}

}

class GroepLidForm extends InlineForm {

	public function __construct(GroepLid $groeplid) {
		parent::__construct($groeplid, 'lidform' . $groeplid->lid_id, Instellingen::get('groepen', 'url') . '/wijzigen/' . $groeplid->groep_id . '/' . $groeplid->lid_id, $field = new TextField('opmerking', $groeplid->opmerking, null, 255, 0, $groeplid));
		$field->setSuggestions(GroepFunctie::getTypeOptions());
	}

}
