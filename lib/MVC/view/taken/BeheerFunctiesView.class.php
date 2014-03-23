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
		$this->smarty->assign('functies', $this->model);
	}

	public function getTitel() {
		return 'Beheer corveefuncties en kwalificaties';
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('MVC/taken/functie/beheer_functies.tpl');
	}

}

class FunctieView extends TemplateView {

	public function __construct(CorveeFunctie $functie) {
		parent::__construct($functie);
		$this->smarty->assign('functie', $this->model);
	}

	public function view() {
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
class FunctieFormView extends PopupForm {

	public function __construct(CorveeFunctie $functie, $actie) {
		parent::__construct($functie, 'taken-functie-form', $actie);
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields[] = new TextField('naam', $functie->naam, 'Functienaam', 25);

		$fields['afk'] = new TextField('afkorting', $functie->afkorting, 'Afkorting', 3);
		$fields['afk']->title = 'Afkorting van de functie';

		$fields['eml'] = new TextareaField('email_bericht', $functie->email_bericht, 'E-mailbericht', 9);
		$fields['eml']->title = 'Tekst in email bericht over deze functie aan de corveeer';

		$fields['ptn'] = new IntField('standaard_punten', $functie->standaard_punten, 'Standaard punten', 0, 10);
		$fields['ptn']->title = 'Aantal corveepunten dat standaard voor deze functie gegeven wordt';

		$fields['k'] = new VinkField('kwalificatie_benodigd', $functie->kwalificatie_benodigd, 'Kwalificatie benodigd');
		$fields['k']->title = 'Is er een kwalificatie benodigd om deze functie uit te mogen voeren';

		$fields[] = new SubmitResetCancel();
		$this->addFields($fields);
	}

	public function getAction() {
		return Instellingen::get('taken', 'url') . '/' . $this->action . '/' . $this->model->functie_id;
	}

	public function getTitel() {
		return 'Corveefunctie ' . $this->action;
	}

}

/**
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 */
class KwalificatieFormView extends PopupForm {

	public function __construct(CorveeKwalificatie $kwalificatie) {
		parent::__construct($kwalificatie, 'taken-kwalificatie-form', Instellingen::get('taken', 'url') . '/kwalificeer/' . $kwalificatie->functie_id);
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('lid_id', $kwalificatie->lid_id, 'Naam of lidnummer', 'leden');
		$fields[] = new SubmitResetCancel();

		$this->addFields($fields);
	}

	public function getTitel() {
		return 'Kwalificatie toewijzen';
	}

}
