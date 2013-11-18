<?php
namespace Taken\MLT;

require_once 'taken/model/InstellingenModel.class.php';
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
				'nieuw' => 'P_CORVEE_MOD',
				'bewerk' => 'P_CORVEE_MOD',
				'opslaan' => 'P_CORVEE_MOD',
				'verwijder' => 'P_CORVEE_MOD'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		$key = null;
		if ($this->hasParam(2)) {
			$key = $this->getParam(2);
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
	
	public function action_nieuw() {
		$instelling = new Instelling();
		$this->content = new InstellingFormView($instelling->getInstellingId(), $instelling->getWaarde());
	}
	
	public function action_bewerk($key) {
		$instelling = InstellingenModel::getInstelling($key);
		$this->content = new InstellingFormView($instelling->getInstellingId(), $instelling->getWaarde());
	}
	
	public function action_opslaan() {
		$form = new InstellingFormView(); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$instelling = InstellingenModel::saveInstelling($values['instelling_id'], $values['waarde']);
			$this->content = new BeheerInstellingenView($instelling);
		} else {
			$this->content = $form;
		}
	}
	
	public function action_verwijder($key) {
		InstellingenModel::verwijderInstelling($key);
		$this->content = new BeheerInstellingenView($key);
	}
}

?>