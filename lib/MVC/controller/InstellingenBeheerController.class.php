<?php

require_once 'MVC/view/InstellingenBeheerView.class.php';
require_once 'MVC/view/form/InstellingFormView.class.php';

/**
 * InstellingenBeheerController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class InstellingenBeheerController extends \AclController {

	/**
	 * Data access model
	 * @var InstellingenModel
	 */
	private $model;

	public function __construct($query) {
		$this->model = InstellingenModel::instance();
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_CORVEE_MOD'
			);
		}
		else {
			$this->acl = array(
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'reset' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$params = array();
		if ($this->hasParam(1)) {
			if ($this->action === 'beheer') {
				$params[] = $this->getParam(1);
			}
			else {
				$params[] = (int) $this->getParam(1);
				if ($this->hasParam(2)) {
					$params[] = $this->getParam(2);
				}
			}
		}
		$this->performAction($params);
	}
	
	public function beheer($module = null) {
		$instellingen = $this->model->getModuleInstellingen($module);
		$this->view = new InstellingenBeheerView($instellingen);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}
	
	public function bewerk($model, $key) {
		$instelling = $this->model->getInstelling($model, $key);
		$this->view = new InstellingFormView($instelling); // fetches POST values itself
	}
	
	public function opslaan($key) {
		$this->bewerk($key); // sets view
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$instelling = $this->model->wijzigInstelling($values['module'], $values['instelling_id'], $values['waarde']);
			$this->view = new InstellingenBeheerView($instelling);
		}
	}
	
	public function reset($module, $key) {
		$this->model->resetInstelling($module, $key);
		$instelling = $this->model->getInstelling($module, $key);
		$this->view = new InstellingenBeheerView($instelling);
	}
}

?>