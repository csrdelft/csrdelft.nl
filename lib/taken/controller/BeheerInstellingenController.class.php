<?php
namespace Taken\MLT;

require_once 'taken/view/BeheerInstellingenView.class.php';
require_once 'taken/view/forms/InstellingFormView.class.php';

/**
 * BeheerInstellingenController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerInstellingenController extends \ACLController {

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
		$this->performAction($key);
	}
	
	public function action_beheer() {
		$instellingen = InstellingenModel::getAlleInstellingen();
		$this->content = new BeheerInstellingenView($instellingen);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('taken.css');
		$this->content->addScript('taken.js');
	}
	
	public function action_bewerk($key) {
		$instelling = InstellingenModel::getInstelling($key);
		$this->content = new InstellingFormView($instelling->getInstellingId(), $instelling->getWaarde()); // fetches POST values itself
	}
	
	public function action_opslaan($key) {
		$this->action_bewerk($key);
		if ($this->content->validate()) {
			$values = $this->content->getValues();
			$instelling = InstellingenModel::saveInstelling($values['instelling_id'], $values['waarde']);
			$this->content = new BeheerInstellingenView($instelling);
		}
	}
	
	public function action_reset($key) {
		InstellingenModel::verwijderInstelling($key);
		$instelling = InstellingenModel::getInstelling($key);
		$this->content = new BeheerInstellingenView($instelling);
	}
}

?>