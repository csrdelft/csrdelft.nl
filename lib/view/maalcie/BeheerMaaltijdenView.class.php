<?php

/**
 * BeheerMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle maaltijden om te beheren.
 * 
 */
//class BeheerMaaltijdenView extends SmartyTemplateView {
//
//	private $prullenbak;
//	private $archief;
//	private $repetities;
//
//	public function __construct($maaltijden, $prullenbak = false, $archief = false, $repetities = null) {
//		parent::__construct($maaltijden);
//		$this->prullenbak = $prullenbak;
//		$this->archief = $archief;
//		$this->repetities = $repetities;
//		if ($prullenbak) {
//			$this->titel = 'Beheer maaltijden in prullenbak';
//		} elseif ($archief) {
//			$this->titel = 'Maaltijdenarchief';
//		} else {
//			$this->titel = 'Maaltijdenbeheer';
//		}
//
//		$this->smarty->assign('prullenbak', $this->prullenbak);
//		$this->smarty->assign('archief', $this->archief);
//	}
//
//	public function view() {
//		$this->smarty->assign('maaltijden', $this->model);
//		$this->smarty->assign('repetities', $this->repetities);
//
//		$this->smarty->display('maalcie/menu_pagina.tpl');
//		$this->smarty->display('maalcie/maaltijd/beheer_maaltijden.tpl');
//	}
//
//}
class BeheerMaaltijdenView extends DataTable {
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer', "Maaltijdenbeheer");

		$weergave = new DataTableKnop('', $this->dataTableId, '', '', "Weergave", 'Weergave van tabel', '', 'collection');
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer?filter=toekomst', '', 'Toekomst', 'Toekomst weergeven', 'time_go', 'sourceChange'));
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer?filter=alles', '', 'Alles', 'Alles weergeven', 'time', 'sourceChange'));
		$weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer?filter=prullenbak', '', 'Prullenbak', 'Prullenbak weergeven', 'bin_closed', 'sourceChange'));
		$this->addKnop($weergave);
	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer";
	}
}

class BeheerMaaltijdenLijst extends DataTableResponse {

}

class BeheerMaaltijdenLijstView extends SmartyTemplateView {

	public function __construct(array $maaltijden) {
		parent::__construct($maaltijden);
	}

	public function view() {
		$this->smarty->assign('prullenbak', false);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		foreach ($this->model as $maaltijd) {
			$this->smarty->assign('maaltijd', $maaltijd);
			$this->smarty->display('maalcie/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}

}

class BeheerMaaltijdView extends SmartyTemplateView {

	private $prullenbak;

	public function __construct(Maaltijd $maaltijd, $prullenbak = false) {
		parent::__construct($maaltijd);
		$this->prullenbak = $prullenbak;
	}

	public function view() {
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('prullenbak', $this->prullenbak);

		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/maaltijd/beheer_maaltijd_lijst.tpl');
	}

}
