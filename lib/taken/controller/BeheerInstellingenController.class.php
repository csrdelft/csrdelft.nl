<?php


require_once 'taken/view/BeheerInstellingenView.class.php';
require_once 'taken/view/forms/InstellingFormView.class.php';

/**
 * BeheerInstellingenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerInstellingenController extends \AclController {

	public function __construct($query) {
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
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$key = null;
		if ($this->hasParam(3)) {
			$key = $this->getParam(3);
		}
		$this->performAction(array($key));
	}
	
	public function beheer() {
		$instellingen = InstellingenModel::getAlleInstellingen();
		$this->view = new BeheerInstellingenView($instellingen);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('taken.css');
		$this->view->addScript('taken.js');
	}
	
	public function bewerk($key) {
		$instelling = InstellingenModel::getInstelling($key);
		$this->view = new InstellingFormView($instelling->getInstellingId(), $instelling->getWaarde()); // fetches POST values itself
	}
	
	public function opslaan($key) {
		$this->bewerk($key);
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$instelling = InstellingenModel::saveInstelling($values['instelling_id'], $values['waarde']);
			$this->view = new BeheerInstellingenView($instelling);
		}
	}
	
	public function reset($key) {
		InstellingenModel::verwijderInstelling($key);
		$instelling = InstellingenModel::getInstelling($key);
		$this->view = new BeheerInstellingenView($instelling);
	}
}

?>