<?php

/**
 * BeheerAbonnementenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle abonnementen en waarschuwingen.
 * 
 */
class BeheerAbonnementenView extends SmartyTemplateView {

	private $repetities;
	private $status;
	private $form;

	public function __construct(array $matrix, array $repetities, $alleenWaarschuwingen = false, $ingeschakeld = null) {
		parent::__construct($matrix, 'Beheer abonnementen');
		$this->repetities = $repetities;

		$field = new LidField('voor_lid', null, 'Toon abonnementen van persoon:', 'allepersonen');
		$this->form = new Formulier(null, 'maalcie-subform-abos', maalcieUrl . '/voorlid');
		$this->form->addFields(array($field));

		$this->status = 'abo';
		if (is_bool($ingeschakeld)) {
			$this->status = ($ingeschakeld ? 'in' : 'abo'); // uit
		}
		if ($alleenWaarschuwingen) {
			$this->status = 'waarschuwing';
		}
	}

	public function view() {
		$this->smarty->assign('form', $this->form);
		$this->smarty->assign('toon', $this->status);

		$this->smarty->assign('aborepetities', MaaltijdRepetitiesModel::getAbonneerbareRepetities());
		$this->smarty->assign('repetities', $this->repetities);
		$this->smarty->assign('matrix', $this->model);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/beheer_abonnementen.tpl');
	}

}

class BeheerAbonnementenLijstView extends SmartyTemplateView {

	public function __construct(array $matrix) {
		parent::__construct($matrix);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td id="maalcie-melding-veld">' . getMelding() . '</td></tr>';
		foreach ($this->model as $vanuid => $abonnementen) {
			$this->smarty->assign('vanuid', $vanuid);
			$this->smarty->assign('abonnementen', $abonnementen);
			$this->smarty->display('maalcie/abonnement/beheer_abonnement_lijst.tpl');
		}
	}

}

class BeheerAbonnementView extends SmartyTemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
	}

	public function view() {
		$this->smarty->assign('abonnement', $this->model);
		$this->smarty->assign('uid', $this->model->getUid());
		$this->smarty->assign('vanuid', $this->model->getVanUid());
		echo '<td id="maalcie-melding-veld">' . getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/beheer_abonnement_veld.tpl');
	}

}
