<?php

namespace CsrDelft\view\maalcie\corvee\taken;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\view\SmartyTemplateView;

/**
 * BeheerTakenView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle taken om te beheren.
 *
 */
class BeheerTakenView extends SmartyTemplateView {

	private $maaltijd;
	private $prullenbak;
	private $repetities;

	/**
	 * BeheerTakenView constructor.
	 * @param array $taken
	 * @param Maaltijd $maaltijd
	 * @param bool $prullenbak
	 * @param null $repetities
	 */
	public function __construct($taken, $maaltijd = null, $prullenbak = false, $repetities = null) {
		parent::__construct(array());
		$this->maaltijd = $maaltijd;
		$this->prullenbak = $prullenbak;
		$this->repetities = $repetities;

		if ($this->maaltijd !== null) {
			$this->titel = 'Maaltijdcorveebeheer: ' . $maaltijd->getTitel();
		} elseif ($prullenbak) {
			$this->titel = 'Beheer corveetaken in prullenbak';
		} else {
			$this->titel = 'Corveebeheer';
		}
		foreach ($taken as $taak) {
			$datum = $taak->datum;
			if (!array_key_exists($datum, $this->model)) {
				$this->model[$datum] = array();
			}
			$this->model[$datum][$taak->functie_id][] = $taak;
		}

		$this->smarty->assign('prullenbak', $this->prullenbak);
	}

	public function view() {
		if ($this->maaltijd !== null) {
			$this->smarty->assign('maaltijd', $this->maaltijd);
			$this->smarty->assign('show', true);
		}
		$this->smarty->assign('taken', $this->model);
		$this->smarty->assign('repetities', $this->repetities);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/beheer_taken.tpl');
	}

}
