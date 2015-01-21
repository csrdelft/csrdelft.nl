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
class GroepenBeheerView extends DataTable {

	public function __construct(GroepenModel $model) {
		parent::__construct($model::orm, null, 'familie_id');
		$this->titel = 'Beheer ' . lcfirst(str_replace('Model', '', get_class($model)));
		$this->dataUrl = groepenUrl . '/beheren';
	}

}

class GroepenView extends SmartyTemplateView {

	/**
	 * Toon CMS pagina
	 * @var string
	 */
	protected $pagina;

	public function __construct(GroepenModel $model, $groepen) {
		parent::__construct($groepen);
		$this->pagina = CmsPaginaModel::get($model::orm);
		if (!$this->pagina) {
			$this->pagina = CmsPaginaModel::get('');
		}
		$this->titel = $this->pagina->titel;
	}

	public function view() {
		$this->smarty->display('groepen/menu_pagina.tpl');
		//$this->smarty->display('groepen/inhoudsopgave.tpl'); //FIXME: cannot iterate more than once over PDO statement of groepen
		$view = new CmsPaginaView($this->pagina);
		$view->view();
		foreach ($this->model as $groep) {
			$view = new GroepView($groep, GroepTab::Lijst);
			$view->view();
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

abstract class GroepTabView implements View {

	protected $groep;

	public function __construct(Groep $groep) {
		$this->groep = $groep;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getModel() {
		return $this->groep;
	}

	public function getTitel() {
		return $this->groep->naam;
	}

}

class GroepLijstView extends GroepTabView {

	private $forms = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->groep->getGroepLeden() as $groeplid) {
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

class GroepPasfotosView extends GroepTabView {

	public function __construct(Groep $groep) {
		parent::__construct($groep);
	}

	public function view() {
		foreach ($this->groep->getGroepLeden() as $groeplid) {
			echo '<div class="pasfoto">' . ProfielModel::getLink($groeplid->uid, 'pasfoto') . '</div>';
		}
	}

}

class GroepStatistiekView extends GroepTabView {

	public function __construct(Groep $groep) {
		parent::__construct($groep->getStatistieken());
	}

	public function view() {
		echo '<table class="groepStats">';
		foreach ($this->groep as $title => $stat) {
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

class GroepEmailsView extends GroepTabView {

	private $emails = array();

	public function __construct(Groep $groep) {
		parent::__construct($groep);
		foreach ($this->groep->getGroepLeden() as $groeplid) {
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
