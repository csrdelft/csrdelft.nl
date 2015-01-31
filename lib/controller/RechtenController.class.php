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
		if (!$this->isPosted()) {
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
		if ($this->isPosted()) {
			$acl = $this->model->find('environment = ? AND (resource = ? OR resource = ?)', array($environment, '*', $resource));
			$this->view = new RechtenData($acl);
		} else {
			$table = new RechtenTable($this->model, $environment, $resource);
			$this->view = new CsrLayoutPage($table);
			$this->view->addCompressedResources('datatable');
		}
	}

	public function aanmaken($environment = null, $resource = null) {
		$ac = $this->model->nieuw($environment, $resource);

		if (!LoginModel::mag('P_ADMIN')) {

			// Recursive permissions
			$rechten = $this->model->get($ac->environment, A::Rechten, $ac->resource);
			if (!$rechten OR ! LoginModel::mag($rechten)) {
				$this->geentoegang();
			}
		}

		$form = new RechtenForm($ac, $this->action);
		if ($form->validate()) {
			$this->model->create($ac);
			$this->view = new RechtenData(array($ac));
		} else {
			$this->view = $form;
		}
	}

	public function wijzigen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!isset($selection[0])) {
			$this->geentoegang();
		}
		$ac = $this->model->getUUID($selection[0]);

		if (!LoginModel::mag('P_ADMIN')) {

			// Recursive permissions
			$rechten = $this->model->get($ac->environment, A::Rechten, $ac->resource);
			if (!$rechten OR ! LoginModel::mag($rechten)) {
				$this->geentoegang();
			}
		}

		$form = new RechtenForm($ac, $this->action);
		if ($form->validate()) {
			$this->model->update($ac);
			$this->view = new RechtenData(array($ac));
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$ac = $this->model->getUUID($UUID);

			if (!LoginModel::mag('P_ADMIN')) {

				// Recursive permissions
				$rechten = $this->model->get($ac->environment, A::Rechten, $ac->resource);
				if (!$rechten OR ! LoginModel::mag($rechten)) {
					continue;
				}
			}

			$this->model->delete($ac);
			$response[] = $ac;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
