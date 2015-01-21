<?php

require_once 'model/entity/groepen/GroepTab.enum.php';
require_once 'model/CmsPaginaModel.class.php';
require_once 'view/CmsPaginaView.class.php';

/**
 * GroepenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GroepenView implements View {

	protected $titel;
	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;
	/**
	 * Lijst van groepen
	 * @var PDOStatement
	 */
	protected $groepen;

	public function __construct(GroepenModel $model, $groepen, $pagina) {
		$this->groepen = $groepen;
		$this->pagina = CmsPaginaModel::instance()->getPagina($model::orm);
		if ($this->pagina) {
			$this->titel = $this->pagina->titel;
		}
	}

	public function getModel() {
		return $this->groepen;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('groepen', $this->groepen);
		$smarty->display('groepen/menu_pagina.tpl');
		//$this->smarty->display('groepen/inhoudsopgave.tpl'); //FIXME: cannot iterate more than once over PDO statement of groepen
		if ($this->pagina) {
			$pagina = new CmsPaginaView($this->pagina);
			$pagina->view();
		}
		foreach ($this->groepen as $groep) {
			$class = get_class($groep) . 'View';
			$class = new $class($groep, GroepTab::Lijst);
			$class->view();
		}
	}

}

class GroepView implements View {

	protected $groep;
	protected $tab;
	protected $tabContent;

	public function __construct(Groep $groep, $groepTab) {
		$this->groep = $groep;
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

	public function getModel() {
		return $this->groep;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return $this->getModel()->naam;
	}

	public function view() {
		$smarty = CsrSmarty::instance();
		$smarty->assign('groep', $this->groep);
		$smarty->assign('tab', $this->tab);
		$smarty->assign('tabContent', $this->tabContent);
		$smarty->display('groepen/groep.tpl'); //TODO: get_class($this->groep)
	}

}

class GroepLijstView extends SmartyTemplateView {

	private $forms = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->model->getGroepLeden() as $groeplid) {
			$this->forms[] = new GroepLidForm($groeplid);
		}
	}

	public function view() {
		echo '<table class="groepLeden"><tbody>';
		foreach ($this->forms as $form) {
			echo '<tr><td>' . ProfielModel::getLink($form->getModel()->uid, 'civitas') . '</td>';
			echo '<td>';
			$form->view();
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

}

class GroepPasfotosView extends SmartyTemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep);
	}

	public function view() {
		foreach ($this->model->getGroepLeden() as $groeplid) {
			echo '<div class="pasfoto">' . ProfielModel::getLink($groeplid->uid, 'pasfoto') . '</div>';
		}
	}

}

class GroepStatistiekView extends SmartyTemplateView {

	public function __construct(Groep $groep) {
		parent::__construct($groep->getStatistieken());
	}

	public function view() {
		echo '<table class="groepStats">';
		foreach ($this->model as $title => $stat) {
			echo '<thead><tr><th colspan="2">' . $title . '</th></tr></thead><tbody>';
			if (!is_array($stat)) {
				echo '<tr><td colspan="2">' . $stat . '</td></tr>';
				continue;
			}
			foreach ($stat as $row) {
				echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
			}
		}
		echo '</tbody></table>';
	}

}

class GroepEmailsView extends SmartyTemplateView {

	private $emails = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->model->getGroepLeden() as $groeplid) {
			$profiel = ProfielModel::get($groeplid->uid);
			if ($profiel AND $profiel->getPrimaryEmail() != '') {
				$this->emails[] = $profiel->getPrimaryEmail();
			}
		}
	}

	public function view() {
		echo '<div class="emails">' . implode(', ', $this->emails) . '</div>';
	}

}

class GroepForm extends Formulier {

	public function __construct(Groep $groep, $action) {
		parent::__construct($groep, 'groepform-' . $groep->id, groepenUrl . '/' . $action . '/' . $groep->id);
		$this->titel = get_class($groep) . ' ' . $action;
		$this->generateFields();
	}

}

class GroepLidForm extends InlineForm {

	public function __construct(GroepLid $groeplid) {
		parent::__construct($groeplid, 'lidform-' . $groeplid->uid, groepenUrl . '/wijzigen/' . $groeplid->groep_id . '/' . $groeplid->uid, $field = new TextField('opmerking', $groeplid->opmerking, null, 255, 0, $groeplid));
		$field->suggestions[] = GroepFunctie::getTypeOptions();
	}

}
