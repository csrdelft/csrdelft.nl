<?php

/**
 * BeheerFunctiesView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle functies om te beheren.
 * 
 */
class BeheerFunctiesView extends SmartyTemplateView {

	public function __construct(array $functies) {
		parent::__construct($functies, 'Beheer corveefuncties en kwalificaties');
	}

	public function view() {
		$this->smarty->assign('functies', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/functie/beheer_functies.tpl');
	}

}

class FunctieView extends SmartyTemplateView {

	public function __construct(CorveeFunctie $functie) {
		parent::__construct($functie);
	}

	public function view() {
		$this->smarty->assign('functie', $this->model);
		$this->smarty->display('maalcie/functie/beheer_functie_lijst.tpl');
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
	}

}

/**
 * Requires id of deleted corveefunctie.
 */
class FunctieDeleteView extends SmartyTemplateView {

	public function view() {
		echo '<tr id="corveefunctie-row-' . $this->model . '" class="remove"></tr>';
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
	}

}

/**
 * Formulier voor een nieuwe of te bewerken corveefunctie.
 */
class FunctieForm extends ModalForm {

	public function __construct(CorveeFunctie $functie, $actie) {
		parent::__construct($functie, 'maalcie-functie-form', maalcieUrl . '/' . $actie . '/' . $functie->functie_id);
		$this->titel = 'Corveefunctie ' . $actie;
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

		$fields['m'] = new VinkField('maaltijden_sluiten', $functie->maaltijden_sluiten, 'Maaltijden sluiten');
		$fields['m']->title = 'Geeft deze functie speciale rechten om maaltijden te mogen sluiten';

		$fields[] = new FormDefaultKnoppen();
		$this->addFields($fields);
	}

}

/**
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 */
class KwalificatieForm extends ModalForm {

	public function __construct(CorveeKwalificatie $kwalificatie) {
		parent::__construct($kwalificatie, 'maalcie-kwalificatie-form', maalcieUrl . '/kwalificeer/' . $kwalificatie->functie_id);
		$this->titel = 'Kwalificatie toewijzen';
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('uid', $kwalificatie->uid, 'Naam of lidnummer', 'leden');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
