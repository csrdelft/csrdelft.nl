<?php

namespace CsrDelft\view\maalcie\abonnementen;

use CsrDelft\model\maalcie\MaaltijdRepetitiesModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerAbonnementenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle abonnementen en waarschuwingen.
 *
 */
class BeheerAbonnementenView extends SmartyTemplateView {

	private $repetities;
	private $status;

	public function __construct(array $matrix, array $repetities, $alleenWaarschuwingen = false, $ingeschakeld = null) {
		parent::__construct($matrix, 'Beheer abonnementen');
		$this->repetities = $repetities;

		$this->status = 'abo';
		if (is_bool($ingeschakeld)) {
			$this->status = ($ingeschakeld ? 'in' : 'abo'); // uit
		}
		if ($alleenWaarschuwingen) {
			$this->status = 'waarschuwing';
		}
	}

	public function view() {
		$this->smarty->assign('toon', $this->status);

		$this->smarty->assign('aborepetities', MaaltijdRepetitiesModel::instance()->find('abonneerbaar = true'));
		$this->smarty->assign('repetities', $this->repetities);
		$this->smarty->assign('matrix', $this->model);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/abonnement/beheer_abonnementen.tpl');
	}

}
