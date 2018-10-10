<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\invoervelden\UrlField;
use CsrDelft\view\JsonResponse;


/**
 * LidInstellingenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property LidInstellingenModel $model
 */
class LidInstellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, LidInstellingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'beheer' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'opslaan' => 'P_LOGGED_IN',
				'reset' => 'P_ADMIN',
				'update' => 'P_LOGGED_IN'
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
		$this->view = view('instellingen.lidinstellingen', [
			'defaultInstellingen' => $this->model->getAll(),
			'instellingen' => $this->model->getAllForLid(LoginModel::getUid())
		]);
	}

	public function POST_update($module, $instelling, $waarde = null) {
		if ($waarde === null) {
			$waarde = filter_input(INPUT_POST, 'waarde', FILTER_SANITIZE_STRING);
		}

		$this->model->wijzigInstelling($module, $instelling, urldecode($waarde));
		$this->view = new JsonResponse(['success' => true]);
	}

	public function opslaan() {
		$this->model->save(); // fetches $_POST values itself
		setMelding('Instellingen opgeslagen', 1);
		$from = new UrlField('referer', HTTP_REFERER, null);
		redirect($from->getValue());
	}

	public function reset($module, $key) {
		$this->model->resetForAll($module, $key);
		setMelding('Voor iedereen de instelling ge-reset naar de standaard waarde', 1);
		$this->view = new JsonResponse(true);
	}

}
