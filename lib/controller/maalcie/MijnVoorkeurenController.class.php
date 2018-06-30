<?php

namespace CsrDelft\controller\maalcie;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\maalcie\forms\EetwensForm;
use CsrDelft\view\maalcie\persoonlijk\voorkeuren\MijnVoorkeurenView;
use CsrDelft\view\maalcie\persoonlijk\voorkeuren\MijnVoorkeurView;


/**
 * MijnVoorkeurenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property CorveeVoorkeurenModel $model
 *
 */
class MijnVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, CorveeVoorkeurenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'mijn' => 'P_CORVEE_IK'
			);
		} else {
			$this->acl = array(
				'inschakelen' => 'P_CORVEE_IK',
				'uitschakelen' => 'P_CORVEE_IK',
				'eetwens' => 'P_CORVEE_IK'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'mijn';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$crid = null;
		if ($this->hasParam(3)) {
			$crid = intval($this->getParam(3));
		}
		parent::performAction(array($crid));
	}

	public function mijn() {
		$voorkeuren = $this->model->getVoorkeurenVoorLid(LoginModel::getUid(), true);
		$this->view = new MijnVoorkeurenView($voorkeuren);
		$this->view = new CsrLayoutPage($this->view);
	}

	public function inschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->inschakelenVoorkeur($voorkeur);
		$this->view = new MijnVoorkeurView($voorkeur);
	}

	public function uitschakelen($crid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = LoginModel::getUid();
		$voorkeur = $this->model->uitschakelenVoorkeur($voorkeur);
		$this->view = new MijnVoorkeurView($voorkeur);
	}

	public function eetwens() {
		$form = new EetwensForm();
		if ($form->validate()) {
			$this->model->setEetwens(LoginModel::getProfiel(), $form->getField()->getValue());
		}
		$this->view = $form;
	}

}
