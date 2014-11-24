<?php

require_once 'MVC/model/happie/MenukaartItemsModel.class.php';
require_once 'MVC/view/happie/MenukaartView.class.php';
require_once 'MVC/view/happie/forms/MenukaartForm.class.php';

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
					parent::performAction(array($item));
				} else {
					$this->geentoegang();
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
			$this->view = new JsonResponse($item);
			return;
		}
		$this->view = $form;
	}

	public function wijzig(HappieMenukaartItem $item) {
		$form = new HappieMenukaartItemWijzigenForm($item);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->update($item);
			$this->view = new JsonResponse($item);
			return;
		}
		$this->view = $form;
	}

}
