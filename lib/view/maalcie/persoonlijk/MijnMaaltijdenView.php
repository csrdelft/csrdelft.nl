<?php

namespace CsrDelft\view\maalcie\persoonlijk;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\maalcie\MaaltijdBeoordelingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\maalcie\forms\MaaltijdKwaliteitBeoordelingForm;
use CsrDelft\view\maalcie\forms\MaaltijdKwantiteitBeoordelingForm;
use CsrDelft\view\SmartyTemplateView;


/**
 * MijnMaaltijdenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 *
 */
class MijnMaaltijdenView extends SmartyTemplateView {

	private $aanmeldingen;
	private $beoordelen = array();
	private $kwantiteit_forms = array();
	private $kwaliteit_forms = array();

	/**
	 * MijnMaaltijdenView constructor.
	 * @param Maaltijd[] $maaltijden
	 * @param array|null $aanmeldingen
	 * @param array $recent
	 */
	public function __construct(array $maaltijden, array $aanmeldingen = null, array $recent) {
		parent::__construct($maaltijden, 'Maaltijdenketzer');
		$this->aanmeldingen = $aanmeldingen;

		foreach ($this->model as $maaltijd) {
			$mid = $maaltijd->maaltijd_id;
			if (!array_key_exists($mid, $this->aanmeldingen)) {
				$this->aanmeldingen[$mid] = false;
			}
		}
		foreach ($recent as $aanmelding) {
			$maaltijd = $aanmelding->maaltijd;
			$mid = $aanmelding->maaltijd_id;
			$this->beoordelen[$mid] = $maaltijd;
			$beoordeling = MaaltijdBeoordelingenModel::instance()->find('maaltijd_id = ? AND uid = ?', array($mid, LoginModel::getUid()))->fetch();
			if (!$beoordeling) {
				$beoordeling = MaaltijdBeoordelingenModel::instance()->nieuw($maaltijd);
			}
			$this->kwantiteit_forms[$mid] = new MaaltijdKwantiteitBeoordelingForm($maaltijd, $beoordeling);
			$this->kwaliteit_forms[$mid] = new MaaltijdKwaliteitBeoordelingForm($maaltijd, $beoordeling);
		}
	}

	public function view() {
		$this->smarty->assign('standaardprijs', intval(InstellingenModel::get('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('aanmeldingen', $this->aanmeldingen);
		$this->smarty->assign('beoordelen', $this->beoordelen);
		$this->smarty->assign('kwantiteit', $this->kwantiteit_forms);
		$this->smarty->assign('kwaliteit', $this->kwaliteit_forms);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijden.tpl');
	}

}
