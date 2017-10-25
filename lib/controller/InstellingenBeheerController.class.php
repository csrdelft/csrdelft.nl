<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\InstellingenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\instellingen\InstellingBeheerView;
use CsrDelft\view\instellingen\InstellingenBeheerView;


/**
 * InstellingenBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @property InstellingenModel $model
 */
class InstellingenBeheerController extends AclController {

	public function __construct($query) {
		parent::__construct($query, InstellingenModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'module' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'opslaan' => 'P_LOGGED_IN',
				'reset' => 'P_LOGGED_IN'
			);
		}
	}

	protected function mag($action, array $args) {
		if (!parent::mag($action, $args)) {
			return false;
		}
		if ($this->hasParam(3)) {
			switch ($this->getParam(3)) {
				case 'agenda':
					return LoginModel::mag('P_AGENDA_MOD');
				case 'corvee':
					return LoginModel::mag('P_CORVEE_MOD');
				case 'maaltijden':
					return LoginModel::mag('P_MAAL_MOD');
				default:
					return LoginModel::mag('P_ADMIN');
			}
		}
		return true; // hoofdpagina: geen module
	}

	public function performAction(array $args = array()) {
		$this->action = 'module';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function module($module = null) {
		$body = new InstellingenBeheerView($this->model, $module);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('maalcie');
	}

	public function opslaan($module, $id) {
		$waarde = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->model->wijzigInstelling($module, $id, $waarde);
		$this->view = new InstellingBeheerView($instelling);
	}

	public function reset($module, $id) {
		$instelling = $this->model->wijzigInstelling($module, $id, $this->model->getDefault($module, $id));
		$this->view = new InstellingBeheerView($instelling);
	}

}
