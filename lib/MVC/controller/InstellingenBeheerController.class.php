<?php

require_once 'MVC/view/InstellingenBeheerView.class.php';

/**
 * InstellingenBeheerController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class InstellingenBeheerController extends AclController {

	/**
	 * Data access model
	 * @var Instellingen
	 */
	private $model;

	public function __construct($query) {
		$this->model = Instellingen::instance();
		parent::__construct($query);
		if (!$this->isPosted()) {
			$this->acl = array(
				'module' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'opslaan' => 'P_LOGGED_IN',
				'reset' => 'P_LOGGED_IN'
			);
		}
		$this->action = 'module';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	protected function hasPermission() {
		if (!parent::hasPermission()) {
			return false;
		}
		if ($this->hasParam(1)) {
			switch ($this->getParam(1)) {
				case 'agenda':
					return LoginLid::instance()->hasPermission('P_AGENDA_MOD');
				case 'corvee':
					return LoginLid::instance()->hasPermission('P_CORVEE_MOD');
				case 'maaltijden':
					return LoginLid::instance()->hasPermission('P_MAAL_MOD');
				default:
					return LoginLid::instance()->hasPermission('P_ADMIN');
			}
		}
		return true; // hoofdpagina: geen module
	}

	public function module($module = null) {
		$body = new InstellingenBeheerView($this->model, $module);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}

	public function opslaan($module, $key) {
		$value = filter_input(INPUT_POST, 'waarde', FILTER_UNSAFE_RAW);
		$instelling = $this->model->wijzigInstelling($module, $key, $value);
		$this->view = new InstellingenBeheerView($this->model, $instelling->module, $instelling);
	}

	public function reset($module, $key) {
		$instelling = $this->model->resetInstelling($module, $key);
		$this->view = new InstellingenBeheerView($this->model, $instelling->module, $instelling);
	}

}
