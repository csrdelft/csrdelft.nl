<?php

require_once 'MVC/model/soccie/SocCieKlantenModel.class.php';
require_once 'MVC/view/soccie/SocCieKlantenView.class.php';

/**
 * SocCieKlantenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class SocCieKlantenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, SocCieKlantenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'saldo' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'saldo';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function saldo() {
		$klant = $this->model->getKlant(LoginModel::getUid());
		$this->view = new SocCieKlantSaldoView($klant);
		$this->view = new CsrLayoutPage($this->getView());
		$this->view->addStylesheet('/layout/css/soccie');
	}

}
