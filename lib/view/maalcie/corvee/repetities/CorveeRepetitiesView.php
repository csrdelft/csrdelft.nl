<?php

namespace CsrDelft\view\maalcie\corvee\repetities;

use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\SmartyTemplateView;

/**
 * CorveeRepetitiesView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle corvee-repetities om te beheren.
 *
 */
class CorveeRepetitiesView extends SmartyTemplateView {

	private $maaltijdrepetitie;

	/**
	 * CorveeRepetitiesView constructor.
	 * @param $repetities
	 * @param MaaltijdRepetitie $maaltijdrepetitie
	 */
	public function __construct($repetities, $maaltijdrepetitie = null) {
		parent::__construct($repetities);
		$this->maaltijdrepetitie = $maaltijdrepetitie;
		if ($this->maaltijdrepetitie !== null) {
			$this->titel = 'Corveebeheer maaltijdrepetitie: ' . $this->maaltijdrepetitie->standaard_titel;
		} else {
			$this->titel = 'Beheer corveerepetities';
		}
	}

	public function view() {
		if ($this->maaltijdrepetitie !== null) {
			$this->smarty->assign('maaltijdrepetitie', $this->maaltijdrepetitie);
		}
		$this->smarty->assign('repetities', $this->model);
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corvee-repetitie/beheer_corvee_repetities.tpl');
	}

}
