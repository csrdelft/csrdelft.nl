<?php

require_once 'MVC/model/entity/groepen/GroepTab.enum.php';
require_once 'MVC/model/CmsPaginaModel.class.php';
require_once 'MVC/view/groepen/GroepTabsView.class.php';
require_once 'MVC/view/CmsPaginaView.class.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class GroepenView extends SmartyTemplateView {

	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct($groepen, $pagina, $titel = false) {
		parent::__construct($groepen, $titel);
		$this->pagina = CmsPaginaModel::instance()->getPagina($pagina);
		if ($this->pagina) {
			$this->titel = $this->pagina->titel;
		}
	}

	public function view() {
		$this->smarty->assign('groepen', $this->model);
		$this->smarty->display('MVC/groepen/menu_pagina.tpl');
		//$this->smarty->display('MVC/groepen/inhoudsopgave.tpl'); //FIXME: cannot iterate more than once over PDO statement of groepen
		if ($this->pagina) {
			$pagina = new CmsPaginaView($this->pagina);
			$pagina->view();
		}
		foreach ($this->model as $groep) {
			$class = get_class($groep) . 'View';
			$class = new $class($groep, GroepTab::Lijst);
			$class->view();
		}
	}

}

abstract class GroepView extends SmartyTemplateView {

	protected $tab;
	protected $tabContent;

	public function __construct(Groep $groep, $groepTab) {
		parent::__construct($groep);
		$this->tab = $groepTab;
		switch ($this->tab) {
			default:
			case GroepTab::Lijst:
				$this->tabContent = new GroepLijstView($groep);
				break;
			case GroepTab::Pasfotos:
				$this->tabContent = new GroepPasfotosView($groep);
				break;
			case GroepTab::Statistiek:
				$this->tabContent = new GroepStatistiekView($groep);
				break;
			case GroepTab::Emails:
				$this->tabContent = new GroepEmailsView($groep);
				break;
		}
	}

	public function view() {
		$this->smarty->assign('groep', $this->model);
		$this->smarty->assign('tab', $this->tab);
		$this->smarty->assign('tabContent', $this->tabContent);
		$this->smarty->display('MVC/groepen/groep.tpl'); //TODO: get_class($this->model)
	}

}

class GroepForm extends Formulier {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, 'groepform-' . $groep->id, Instellingen::get('groepen', 'url') . '/' . $action . '/' . $groep->id);
		$this->titel = get_class($groep) . ' ' . $action;
		$this->generateFields();
	}

}

class GroepLidForm extends InlineForm {

	public function __construct(GroepLid $groeplid) {
		parent::__construct($groeplid, 'lidform-' . $groeplid->uid, Instellingen::get('groepen', 'url') . '/wijzigen/' . $groeplid->groep_id . '/' . $groeplid->uid, $field = new TextField('opmerking', $groeplid->opmerking, null, 255, 0, $groeplid));
		$field->suggestions = GroepFunctie::getTypeOptions();
	}

}
