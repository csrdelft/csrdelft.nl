<?php

require_once 'model/maalcie/CorveeTakenModel.class.php';
require_once 'model/maalcie/CorveePuntenModel.class.php';
require_once 'model/maalcie/CorveeVrijstellingenModel.class.php';
require_once 'view/maalcie/MijnCorveeView.class.php';
require_once 'view/maalcie/CorveeRoosterView.class.php';

/**
 * MijnCorveeController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeTakenModel $model
 * 
 */
class MijnCorveeController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeTakenModel::instance());
		if ($this->getMethod() == 'GET') {
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
		$taken = $this->model->getKomendeTakenVoorLid(LoginModel::getUid());
		$rooster = $this->model->getRoosterMatrix($taken);
		$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$punten = CorveePuntenModel::loadPuntenVoorLid(LoginModel::getProfiel(), $functies);
		$vrijstelling = CorveeVrijstellingenModel::instance()->getVrijstelling(LoginModel::getUid());
		$this->view = new MijnCorveeView($rooster, $punten, $functies, $vrijstelling);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

	public function rooster($toonverleden = false) {
		if ($toonverleden === 'verleden' AND LoginModel::mag('P_CORVEE_MOD')) {
			$taken = $this->model->getVerledenTaken();
			$toonverleden = false; // hide button
		} else {
			$taken = $this->model->getKomendeTaken();
			$toonverleden = LoginModel::mag('P_CORVEE_MOD');
		}
		$rooster = $this->model->getRoosterMatrix($taken);
		$this->view = new CorveeRoosterView($rooster, $toonverleden);
		$this->view = new CsrLayoutPage($this->view);
		$this->view->addCompressedResources('maalcie');
	}

}
