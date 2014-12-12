<?php

require_once 'model/happie/MenukaartItemsModel.class.php';
require_once 'view/happie/MenukaartView.class.php';
require_once 'view/happie/forms/MenukaartForm.class.php';

/**
 * MenukaartItemsController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de Happietaria menukaart-items.
 * 
 */
class HappieMenukaartItemsController extends AclController {

	public function __construct($query) {
		parent::__construct($query, HappieMenukaartItemsModel::instance());
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

				$field = new ObjectIdField(new HappieMenukaartItem());
				if ($this->isPosted() AND $field->validate()) {
					$ids = $field->getValue();
					$item = $this->model->getItem((int) $ids[0]);
					if (!$item) {
						$this->geentoegang();
					}
					parent::performAction(array($item)); // set view form
				} else {
					$this->geentoegang();
				}
				if ($this->view->validate()) {
					$this->model->update($item);
					$this->view = new HappieMenukaartItemsData(array($item));
				}
				break;

			default:
				parent::performAction($this->getParams(4));
		}
	}

	public function overzicht() {
		if ($this->isPosted()) {
			$data = $this->model->find();
			$this->view = new HappieMenukaartItemsData($data);
		} else {
			$body = new HappieMenukaartItemsView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function nieuw() {
		$item = $this->model->newItem();
		$form = new HappieMenukaartItemForm($item);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->create($item);
			$this->view = new HappieMenukaartItemsData(array($item));
			return;
		}
		$this->view = $form;
	}

	public function wijzig(HappieMenukaartItem $item) {
		$this->view = new HappieMenukaartItemWijzigenForm($item);
	}

}
