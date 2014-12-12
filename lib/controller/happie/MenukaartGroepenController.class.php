<?php

require_once 'model/happie/MenukaartGroepenModel.class.php';
require_once 'view/happie/MenukaartView.class.php';
require_once 'view/happie/forms/MenukaartForm.class.php';

/**
 * MenukaartGroepenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de Happietaria menukaart-groepen.
 * 
 */
class HappieMenukaartGroepenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, HappieMenukaartGroepenModel::instance());
		$this->acl = array(
			'overzicht'	 => 'groep:2014',
			'nieuw'		 => 'groep:2014',
			'wijzig'	 => 'groep:2014'
		);
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		switch ($this->action) {
			case 'wijzig':

				$field = new ObjectIdField(new HappieMenukaartGroep());
				if ($this->isPosted() AND $field->validate()) {
					$ids = $field->getValue();
					$groep = $this->model->getGroep((int) $ids[0]);
					if (!$groep) {
						$this->geentoegang();
					}
					parent::performAction(array($groep)); // set view form
				} else {
					$this->geentoegang();
				}
				if ($this->view->validate()) {
					$this->model->update($groep);
					$this->view = new HappieMenukaartGroepenData(array($groep));
				}
				break;

			default:
				parent::performAction($this->getParams(4));
		}
	}

	public function overzicht() {
		if ($this->isPosted()) {
			$data = $this->model->find();
			$this->view = new HappieMenukaartGroepenData($data);
		} else {
			$body = new HappieMenukaartGroepenView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function nieuw() {
		$groep = $this->model->newGroep();
		$form = new HappieMenukaartGroepForm($groep);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->update($groep);
			$this->view = new HappieMenukaartGroepenData(array($groep));
			return;
		}
		$this->view = $form;
	}

	public function wijzig(HappieMenukaartGroep $groep) {
		$this->view = new HappieMenukaartGroepWijzigenForm($groep);
	}

}
