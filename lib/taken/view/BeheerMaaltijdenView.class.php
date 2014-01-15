<?php

/**
 * BeheerMaaltijdenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijden om te beheren.
 * 
 */
class BeheerMaaltijdenView extends TemplateView {

	private $_maaltijden;
	private $_prullenbak;
	private $_archief;
	private $_repetities;
	private $_popup;

	public function __construct($maaltijden, $prullenbak = false, $archief = false, $repetities = null, $popup = null) {
		parent::__construct();
		$this->_maaltijden = $maaltijden;
		$this->_prullenbak = $prullenbak;
		$this->_archief = $archief;
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}

	public function getTitel() {
		if ($this->_prullenbak) {
			return 'Beheer maaltijden in prullenbak';
		} elseif ($this->_archief) {
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
		if (is_array($this->_maaltijden)) { // list of maaltijden
			if ($this->_prullenbak || $this->_archief || $this->_repetities !== null) { // normal view
				$this->smarty->assign('prullenbak', $this->_prullenbak);
				$this->smarty->assign('archief', $this->_archief);
				$this->smarty->assign('popup', $this->_popup);
				$this->smarty->assign('melding', $this->getMelding());
				$this->smarty->assign('kop', $this->getTitel());
				$this->smarty->display('taken/menu_pagina.tpl');

				$this->smarty->assign('maaltijden', $this->_maaltijden);
				$this->smarty->assign('repetities', $this->_repetities);
				$this->smarty->display('taken/maaltijd/beheer_maaltijden.tpl');
			} else { // list of new maaltijden
				foreach ($this->_maaltijden as $maaltijd) {
					$this->smarty->assign('maaltijd', $maaltijd);
					$this->smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
				}
			}
		} elseif (is_int($this->_maaltijden)) { // id of deleted maaltijd
			echo '<tr id="maaltijd-row-' . $this->_maaltijden . '" class="remove"></tr>';
		} else { // single maaltijd
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			$this->smarty->assign('maaltijd', $this->_maaltijden);
			$this->smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}

}

?>