<?php

require_once 'taken/model/AbonnementenModel.class.php';
require_once 'taken/model/MaaltijdRepetitiesModel.class.php';
require_once 'taken/view/MijnAbonnementenView.class.php';

/**
 * MijnAbonnementenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MijnAbonnementenController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'mijn' => 'P_MAAL_IK'
			);
		} else {
			$this->acl = array(
				'inschakelen' => 'P_MAAL_IK',
				'uitschakelen' => 'P_MAAL_IK'
			);
		}
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$mrid = null;
		if ($this->hasParam(3)) {
			$mrid = intval($this->getParam(3));
		}
		$this->performAction(array($mrid));
	}

	public function mijn() {
		$abonnementen = AbonnementenModel::getAbonnementenVoorLid(\LoginLid::instance()->getUid(), true, true);
		$this->view = new MijnAbonnementenView($abonnementen);
		$this->view = new CsrLayoutPage($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

	public function inschakelen($mrid) {
		$abo_aantal = AbonnementenModel::inschakelenAbonnement($mrid, \LoginLid::instance()->getUid());
		$this->view = new MijnAbonnementenView($abo_aantal[0]);
		if ($abo_aantal[1] > 0) {
			setMelding('Automatisch aangemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en'), 2);
			DebugLogModel::instance()->log(get_called_class(), 'inschakelen', array('aangemeld voor ' . $abo_aantal[1] . ' maaltijden'));
		}
	}

	public function uitschakelen($mrid) {
		$abo_aantal = AbonnementenModel::uitschakelenAbonnement($mrid, \LoginLid::instance()->getUid());
		$this->view = new MijnAbonnementenView($mrid);
		if ($abo_aantal[1] > 0) {
			setMelding('Automatisch afgemeld voor ' . $abo_aantal[1] . ' maaltijd' . ($abo_aantal[1] === 1 ? '' : 'en'), 2);
			DebugLogModel::instance()->log(get_called_class(), 'inschakelen', array('afgemeld voor ' . $abo_aantal[1] . ' maaltijden'));
		}
	}

}

?>