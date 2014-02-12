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
				'module' => 'P_LEDEN_READ'
			);
		} else {
			$this->acl = array(
				'opslaan' => 'P_LEDEN_READ',
				'reset' => 'P_LEDEN_READ'
			);
		}
		$this->action = 'module';
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$this->performAction($this->getParams(1));
	}

	protected function hasPermission() {
		if (!parent::hasPermission()) {
			return false;
		}
		if ($this->hasParam(1)) {
			switch ($this->getParam(1)) {
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
		$this->view = new InstellingenBeheerView($this->model, $module);
		$this->view = new csrdelft($this->getContent());
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
