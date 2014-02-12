<?php

/**
 * BeheerMaaltijdenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijden om te beheren.
 * 
 */
class BeheerMaaltijdenView extends TemplateView {

	private $prullenbak;
	private $archief;
	private $repetities;

	public function __construct($maaltijden, $prullenbak = false, $archief = false, $repetities = null) {
		parent::__construct($maaltijden);
		$this->prullenbak = $prullenbak;
		$this->archief = $archief;
		$this->repetities = $repetities;
	}

	public function getTitel() {
		if ($this->prullenbak) {
			return 'Beheer maaltijden in prullenbak';
		} elseif ($this->archief) {
			return 'Maaltijdenarchief';
		} else {
			return 'Beheer maaltijden';
		}
	}

	public function getLidLink($uid) {
		$lid = \LidCache::getLid($uid);
		if ($lid instanceof \Lid) {
			return $lid->getNaamLink(Instellingen::get('maaltijden', 'weergave_ledennamen_beheer'), Instellingen::get('maaltijden', 'weergave_link_ledennamen'));
		}
		return $uid;
	}

	public function view() {
		if (is_array($this->model)) { // list of maaltijden
			if ($this->prullenbak || $this->archief || $this->repetities !== null) { // normal view
				$this->smarty->assign('prullenbak', $this->prullenbak);
				$this->smarty->assign('archief', $this->archief);
				$this->smarty->display('taken/menu_pagina.tpl');
				$this->smarty->assign('maaltijden', $this->model);
				$this->smarty->assign('repetities', $this->repetities);
				$this->smarty->display('taken/maaltijd/beheer_maaltijden.tpl');
			} else { // list of new maaltijden
				foreach ($this->model as $maaltijd) {
					$this->smarty->assign('maaltijd', $maaltijd);
					$this->smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
				}
			}
		} elseif (is_int($this->model)) { // id of deleted maaltijd
			echo '<tr id="maaltijd-row-' . $this->model . '" class="remove"></tr>';
		} else { // single maaltijd
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			$this->smarty->assign('maaltijd', $this->model);
			$this->smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}

}
