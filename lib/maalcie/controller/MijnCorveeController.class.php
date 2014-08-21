<?php

require_once 'maalcie/model/CorveeTakenModel.class.php';
require_once 'maalcie/model/CorveePuntenModel.class.php';
require_once 'maalcie/model/CorveeVrijstellingenModel.class.php';
require_once 'maalcie/view/MijnCorveeView.class.php';
require_once 'maalcie/view/CorveeRoosterView.class.php';

/**
 * MijnCorveeController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MijnCorveeController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'mijn'		 => 'P_CORVEE_IK',
				'rooster'	 => 'P_CORVEE_IK'
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
		$taken = CorveeTakenModel::getKomendeTakenVoorLid(LoginModel::getUid());
		$rooster = CorveeTakenModel::getRoosterMatrix($taken);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$punten = CorveePuntenModel::loadPuntenVoorLid(LoginModel::instance()->getLid(), $functies);
		$vrijstelling = CorveeVrijstellingenModel::getVrijstelling(LoginModel::getUid());
		$this->view = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet('/layout/css/taken.css');
		$this->view->addScript('/layout/js/taken.js');
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' AND LoginModel::mag('P_CORVEE_MOD')) {
			$taken = CorveeTakenModel::getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = CorveeTakenModel::getKomendeTaken();
			$toonverleden = LoginModel::mag('P_CORVEE_MOD');
		}
		$rooster = CorveeTakenModel::getRoosterMatrix($taken);
		$this->view = new CorveeRoosterView($rooster, $toonverleden);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet('/layout/css/taken.css');
		$this->view->addScript('/layout/js/taken.js');
	}

}
