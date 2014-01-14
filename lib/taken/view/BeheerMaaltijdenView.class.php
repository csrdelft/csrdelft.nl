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
			return $lid->getNaamLink($GLOBALS['maaltijden']['weergave_ledennamen_beheer'], $GLOBALS['maaltijden']['weergave_ledennamen']);
		}
		return $uid;
	}

	public function view() {
		if (is_array($this->_maaltijden)) { // list of maaltijden
			if ($this->_prullenbak || $this->_archief || $this->_repetities !== null) { // normal view
				$this->assign('prullenbak', $this->_prullenbak);
				$this->assign('archief', $this->_archief);
				$this->assignByRef('this', $this);
				$this->assign('popup', $this->_popup);
				$this->assign('melding', $this->getMelding());
				$this->assign('kop', $this->getTitel());
				$this->display('taken/menu_pagina.tpl');

				$this->assign('maaltijden', $this->_maaltijden);
				$this->assign('repetities', $this->_repetities);
				$this->display('taken/maaltijd/beheer_maaltijden.tpl');
			} else { // list of new maaltijden
				foreach ($this->_maaltijden as $maaltijd) {
					$this->assign('maaltijd', $maaltijd);
					$this->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
				}
			}
		} elseif (is_int($this->_maaltijden)) { // id of deleted maaltijd
			echo '<tr id="maaltijd-row-' . $this->_maaltijden . '" class="remove"></tr>';
		} else { // single maaltijd
			echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
			$this->assign('maaltijd', $this->_maaltijden);
			$this->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}

}

?>