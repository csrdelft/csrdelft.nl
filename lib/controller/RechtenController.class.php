<?php

require_once 'view/RechtenView.class.php';

/**
 * RechtenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de ACL.
 */
class RechtenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, AccessModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'bekijken' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'bekijken'		 => 'P_LOGGED_IN',
				'aanmaken'		 => 'P_LOGGED_IN',
				'wijzigen'		 => 'P_LOGGED_IN',
				'verwijderen'	 => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function bekijken($environment = null, $resource = null) {
		if ($this->getMethod() == 'POST') {
			$acl = $this->model->getTree($environment, $resource);
			$this->view = new RechtenData($acl);
		} else {
			$table = new RechtenTable($this->model, $environment, $resource);
			$this->view = new CsrLayoutPage($table);
		}
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->model->nieuw($environment, $resource);
		$form = new RechtenForm($ac, $this->action);
		if ($form->validate()) {
			$this->model->setAcl($ac->environment, $ac->resource, array(
				$ac->action => $ac->subject
			));
			$this->view = new RechtenData(array($ac));
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!isset($selection[0])) {
			return $this->geentoegang();
		}
		$ac = $this->model->retrieveByUUID($selection[0]);
		$form = new RechtenForm($ac, $this->action);
		if ($form->validate()) {
			$this->model->setAcl($ac->environment, $ac->resource, array(
				$ac->action => $ac->subject
			));
			$this->view = new RechtenData(array($ac));
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$ac = $this->model->retrieveByUUID($UUID);
			$this->model->setAcl($ac->environment, $ac->resource, array(
				$ac->action => null
			));
			$response[] = $ac;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
