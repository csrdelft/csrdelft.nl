<?php

/**
 * BeheerFunctiesView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle functies om te beheren.
 * 
 */
class BeheerFunctiesView extends TemplateView {

	public function __construct(array $functies) {
		parent::__construct($functies);
	}

	public function getTitel() {
		return 'Beheer corveefuncties en kwalificaties';
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');

		$this->smarty->assign('functies', $this->model);
		$this->smarty->display('MVC/taken/functie/beheer_functies.tpl');
	}

}

class FunctieView extends TemplateView {

	public function __construct(CorveeFunctie $functie) {
		parent::__construct($functie);
	}

	public function view() {
		$this->smarty->assign('functie', $this->model);
		$this->smarty->display('MVC/taken/functie/beheer_functie_lijst.tpl');
		echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
	}

}

/**
 * Requires id of deleted corveefunctie.
 */
class FunctieDeleteView extends TemplateView {

	public function view() {
		echo '<tr id="corveefunctie-row-' . $this->model . '" class="remove"></tr>';
		echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
	}

}

/**
 * Formulier voor een nieuwe of te bewerken corveefunctie.
 */
class FunctieFormView extends Formulier {

	public function __construct(CorveeFunctie $functie, $actie) {
		parent::__construct($functie, 'taken-functie-form', $actie);
		$this->css_classes[] = 'popup';
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields[] = new TextField('naam', $functie->naam, 'Naam', 25);
		$fields[] = new TextField('afkorting', $functie->afkorting, 'Afkorting', 3);
		$fields[] = new TextareaField('email_bericht', $functie->email_bericht, 'Email', 9);
		$fields[] = new IntField('standaard_punten', $functie->standaard_punten, 'Standaard punten', 10, 0);
		$fields[] = new VinkField('kwalificatie_benodigd', $functie->kwalificatie_benodigd, 'Kwalificatie benodigd');
		$fields[] = new SubmitResetCancel();
		$this->addFields($fields);
	}

	public function getAction() {
		return Instellingen::get('taken', 'url') . '/' . $this->action . '/' . $this->model->functie_id;
	}

	public function view() {
		echo '<div id="popup-content"><h1>Corveefunctie ' . $this->action . '</h1>';
		echo parent::view();
		echo '</div>';
	}

}
