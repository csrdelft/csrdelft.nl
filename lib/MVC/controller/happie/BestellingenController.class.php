<?php

require_once 'MVC/view/happie/BestellingenView.class.php';

/**
 * BestellingenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de Happietaria bestellingen.
 * 
 */
class HappieBestellingenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, HappieBestellingenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'overzicht'	 => 'groep:2014',
				'keuken'	 => 'groep:2014',
				'serveer'	 => 'groep:2014',
				'bar'		 => 'groep:2014',
				'data'		 => 'groep:2014',
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

	public function overzicht() {
		$body = new HappieBestellingenView();
		$this->view = new CsrLayout3Page($body);
	}

	public function keuken() {
		$body = new HappieKeukenView();
		$this->view = new CsrLayout3Page($body);
	}

	public function serveer() {
		$body = new HappieServeerView();
		$this->view = new CsrLayout3Page($body);
	}

	public function bar() {
		$body = new HappieBarView();
		$this->view = new CsrLayout3Page($body);
	}

	public function data($y = null, $m = null, $d = null) {
		$y = (int) $y;
		$m = (int) $m;
		$d = (int) $d;
		if (checkdate($m, $d, $y)) {
			$date = $y . '-' . $m . '-' . $d;
		} else {
			$date = date('Y-m-d');
		}
		$data = $this->model->find('datum = ?', array($date));
		$this->view = new HappieBestellingenJson($data);
	}

	public function nieuw() {
		$form = new HappieBestelForm();
		if ($this->isPosted() AND $form->validate()) {
			foreach ($form->getValues() as $item_id => $attr) {
				$this->newBestelling($attr['tafel'], $item_id, $attr['aantal'], $attr['klant_allergie']);
			}
			setMelding('Bestelling succesvol toegevoegd', 1);
			$this->overzicht();
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
		if ($this->isPosted() AND $form->validate()) {
			$this->model->update($bestelling);
			setMelding('Wijziging succesvol opgeslagen', 1);
			$this->overzicht();
			return;
		}
		$this->view = new CsrLayout3Page($form);
	}

}
