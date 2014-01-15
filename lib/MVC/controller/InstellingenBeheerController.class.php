<?php

require_once 'MVC/view/InstellingenBeheerView.class.php';
require_once 'MVC/view/form/InstellingFormView.class.php';

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
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'module' => 'P_LEDEN_READ'
			);
		} else {
			$this->acl = array(
				'bewerk' => 'P_LEDEN_READ',
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

	public function bewerk($model, $key) {
		$instelling = $this->model->getInstelling($model, $key);
		$this->view = new InstellingFormView($instelling); // fetches POST values itself
	}

	public function opslaan($model, $key) {
		$this->bewerk($model, $key); // sets view
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$instelling = $this->model->wijzigInstelling($values['module'], $values['instelling_id'], $values['waarde']);
			$this->view = new InstellingenBeheerView($this->model, $instelling->module, $instelling);
		}
	}

	public function reset($module, $key) {
		$instelling = $this->model->resetInstelling($module, $key);
		$this->view = new InstellingenBeheerView($this->model, $instelling->module, $instelling);
	}

}

?>