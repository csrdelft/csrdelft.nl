<?php

require_once 'maalcie/model/MaaltijdAbonnementenModel.class.php';
require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';
require_once 'maalcie/view/MijnAbonnementenView.class.php';

/**
 * MijnAbonnementenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MijnAbonnementenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'mijn' => 'P_MAAL_IK'
			);
		} else {
			$this->acl = array(
				'inschakelen'	 => 'P_MAAL_IK',
				'uitschakelen'	 => 'P_MAAL_IK'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = (int) $this->getParam(3);
		}
		parent::performAction(array($mrid));
	}

	public function mijn() {
		$abonnementen = MaaltijdAbonnementenModel::getAbonnementenVoorLid(LoginModel::getUid(), true, true);
		$this->view = new MijnAbonnementenView($abonnementen);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet($this->view->getCompressedStyleUrl('layout', 'maalcie'), true);
		$this->view->addScript($this->view->getCompressedScriptUrl('layout', 'maalcie'), true);
	}

	public function inschakelen($mrid) {
		$abo_aantal = MaaltijdAbonnementenModel::inschakelenAbonnement($mrid, LoginModel::getUid());
		$this->view = new MijnAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch aangemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

	public function uitschakelen($mrid) {
		$abo_aantal = MaaltijdAbonnementenModel::uitschakelenAbonnement($mrid, LoginModel::getUid());
		$this->view = new MijnAbonnementView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			$melding = 'Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en');
			setMelding($melding, 2);
		}
	}

}
