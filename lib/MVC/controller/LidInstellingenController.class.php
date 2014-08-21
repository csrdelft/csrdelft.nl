<?php

require_once 'MVC/view/LidInstellingenView.class.php';

/**
 * LidInstellingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LidInstellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, LidInstellingen::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_LOGGED_IN',
				'reset' => 'P_ADMIN'
			);
		} else {
			$this->acl = array(
				'opslaan' => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer() {
		$body = new LidInstellingenView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

	public function opslaan() {
		$this->model->save(); // fetches $_POST values itself
		invokeRefresh(CSR_ROOT . '/', 'Instellingen opgeslagen', 1);
	}

	public function reset($module, $key, $value) {
		$this->model->setForAll($module, $key, $value);
		invokeRefresh(CSR_ROOT . '/instellingen/beheer', 'Voor iedereen de instelling overschreven', 1);
	}

}
