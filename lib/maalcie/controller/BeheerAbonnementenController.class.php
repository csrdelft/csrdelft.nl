<?php

require_once 'maalcie/model/MaaltijdAbonnementenModel.class.php';
require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';
require_once 'maalcie/view/BeheerAbonnementenView.class.php';

/**
 * BeheerMaaltijdenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class BeheerAbonnementenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'waarschuwingen' => 'P_MAAL_MOD',
				'ingeschakeld'	 => 'P_MAAL_MOD',
				'abonneerbaar'	 => 'P_MAAL_MOD'
			);
		} else {
			$this->acl = array(
				'inschakelen'	 => 'P_MAAL_MOD',
				'uitschakelen'	 => 'P_MAAL_MOD',
				'voorlid'		 => 'P_MAAL_MOD',
				'novieten'		 => 'P_MAAL_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'waarschuwingen';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	private function beheer($alleenWaarschuwingen, $ingeschakeld = null) {
		$matrix_repetities = MaaltijdAbonnementenModel::getAbonnementenMatrix(false, $alleenWaarschuwingen, $ingeschakeld);
		$this->view = new BeheerAbonnementenView($matrix_repetities[0], $matrix_repetities[1], $alleenWaarschuwingen, $ingeschakeld);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet($this->view->getCompressedStyleUrl('layout', 'maalcie'), true);
		$this->view->addScript($this->view->getCompressedScriptUrl('layout', 'maalcie'), true);
	}

	public function waarschuwingen() {
		$this->beheer(true, null);
	}

	public function ingeschakeld() {
		$this->beheer(false, true);
	}

	public function abonneerbaar() {
		$this->beheer(false, false);
	}

	public function voorlid() {
		$InputField = new LidField('voor_lid', null, null, 'allepersonen'); // fetches POST values itself
		if ($InputField->validate()) {
			$uid = $InputField->getValue();
			$matrix = array();
			$matrix[$uid] = MaaltijdAbonnementenModel::getAbonnementenVoorLid($uid, false, true);
			$this->view = new BeheerAbonnementenLijstView($matrix);
		} else {
			$this->view = new BeheerAbonnementenLijstView(array());
			setMelding($InputField->error, -1);
		}
	}

	public function novieten() {
		$mrid = (int) filter_input(INPUT_POST, 'mrid', FILTER_SANITIZE_NUMBER_INT);
		$aantal = MaaltijdAbonnementenModel::inschakelenAbonnementVoorNovieten($mrid);
		$matrix = MaaltijdAbonnementenModel::getAbonnementenVanNovieten();
		$novieten = sizeof($matrix);
		$this->view = new BeheerAbonnementenLijstView($matrix);
		setMelding(
				$aantal . ' abonnement' . ($aantal !== 1 ? 'en' : '') . ' aangemaakt voor ' .
				$novieten . ' noviet' . ($novieten !== 1 ? 'en' : '') . '.', 1);
	}

	public function inschakelen($mrid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$abo_aantal = MaaltijdAbonnementenModel::inschakelenAbonnement((int) $mrid, $uid);
		$this->view = new BeheerAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch aangemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

	public function uitschakelen($mrid, $uid) {
		if (!Lid::exists($uid)) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		$abo_aantal = MaaltijdAbonnementenModel::uitschakelenAbonnement((int) $mrid, $uid);
		$this->view = new BeheerAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

}
