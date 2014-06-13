<?php

require_once 'taken/model/TakenModel.class.php';
require_once 'taken/model/PuntenModel.class.php';
require_once 'taken/model/VrijstellingenModel.class.php';
require_once 'taken/view/MijnCorveeView.class.php';
require_once 'taken/view/CorveeRoosterView.class.php';

/**
 * MijnCorveeController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MijnCorveeController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'mijn' => 'P_CORVEE_IK',
				'rooster' => 'P_CORVEE_IK'
			);
		} else {
			$this->acl = array();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function mijn() {
		$taken = TakenModel::getKomendeTakenVoorLid(LoginLid::instance()->getUid());
		$rooster = TakenModel::getRoosterMatrix($taken);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$punten = PuntenModel::loadPuntenVoorLid(LoginLid::instance()->getLid(), $functies);
		$vrijstelling = VrijstellingenModel::getVrijstelling(LoginLid::instance()->getUid());
		$this->view = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		$this->view = new CsrLayoutPage($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' AND LoginLid::mag('P_CORVEE_MOD')) {
			$taken = TakenModel::getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = TakenModel::getKomendeTaken();
			$toonverleden = LoginLid::mag('P_CORVEE_MOD');
		}
		$rooster = TakenModel::getRoosterMatrix($taken);
		$this->view = new CorveeRoosterView($rooster, $toonverleden);
		$this->view = new CsrLayoutPage($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

}
