<?php

require_once 'view/maalcie/forms/MaaltijdBeoordelingForm.class.php';

/**
 * MijnMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends SmartyTemplateView {

	private $aanmeldingen;
	private $beoordelen;
	private $kwantiteit_forms;
	private $kwaliteit_forms;

	public function __construct(array $maaltijden, array $aanmeldingen = null, array $beoordelen = array()) {
		parent::__construct($maaltijden, 'Maaltijdenketzer');
		$this->aanmeldingen = $aanmeldingen;
		foreach ($this->model as $maaltijd) {
			$mid = $maaltijd->getMaaltijdId();
			if (!array_key_exists($mid, $this->aanmeldingen)) {
				$this->aanmeldingen[$mid] = false;
			}
		}
		$this->beoordelen = $beoordelen;
		$this->kwantiteit_forms = array();
		foreach ($beoordelen as $maaltijd) {
			$beoordeling = MaaltijdBeoordelingenModel::instance()->find('maaltijd_id = ? AND uid = ?', array($maaltijd->getMaaltijdId(), LoginModel::getUid()))->fetch();
			if (!$beoordeling) {
				$beoordeling = MaaltijdBeoordelingenModel::instance()->nieuw($maaltijd);
			}
			$this->kwantiteit_forms[$maaltijd->getMaaltijdId()] = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
			$this->kwaliteit_forms[$maaltijd->getMaaltijdId()] = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
	}

	public function view() {
		$this->smarty->assign('standaardprijs', intval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('aanmeldingen', $this->aanmeldingen);
		$this->smarty->assign('beoordelen', $this->beoordelen);
		$this->smarty->assign('kwantiteit', $this->kwantiteit_forms);
		$this->smarty->assign('kwaliteit', $this->kwaliteit_forms);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijden.tpl');
	}

}

class MijnMaaltijdView extends SmartyTemplateView {

	private $aanmelding;

	public function __construct(Maaltijd $maaltijd, MaaltijdAanmelding $aanmelding = null) {
		parent::__construct($maaltijd);
		$this->aanmelding = $aanmelding;
	}

	public function view() {
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $this->aanmelding);
		$this->smarty->assign('standaardprijs', intval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijd_lijst.tpl');
	}

}
