<?php

require_once 'MVC/view/happie/BestellingenView.class.php';

/**
 * BestellingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de agenda.
 */
class HappieBestellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, HappieBestellingenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'overzicht'	 => 'groep:2014',
				'nieuw'		 => 'groep:2014'
			);
		} else {
			$this->acl = array(
				'nieuw'	 => 'groep:2014',
				'wijzig' => 'groep:2014'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		parent::performAction($this->getParams(4));
	}

	public function overzicht($data = null) {
		if ($data === 'data') {
			$this->view = new JsonResponse($this->model->find());
		} else {
			$body = new HappieBestellingenView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function nieuw() {
		$form = new HappieBestelForm();
		if ($this->isPosted() AND $form->validate()) {
			foreach ($form->getValues() as $item_id => $attr) {
				$this->newBestelling($attr['tafel'], $item_id, $attr['aantal'], $attr['klant_allergie']);
			}
			$this->view = new JsonResponse(true);
			return;
		}
		$this->view = new CsrLayout3Page($form);
	}

	public function wijzig($id) {
		$bestelling = $this->model->getBestelling((int) $id);
		if (!$bestelling) {
			$this->overzicht();
			return;
		}
		$form = new HappieBestellingWijzigenForm($bestelling);
		$this->view = new CsrLayout3Page($form);
	}

}
