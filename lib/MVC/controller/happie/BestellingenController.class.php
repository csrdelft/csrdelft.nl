<?php

require_once 'MVC/model/happie/BestellingenModel.class.php';
require_once 'MVC/view/happie/BestellingenView.class.php';
require_once 'MVC/view/happie/forms/BestelForm.class.php';

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
		$this->acl = array(
			'overzicht'			 => 'groep:2014',
			'serveer'			 => 'groep:2014',
			'keuken'			 => 'groep:2014',
			'bar'				 => 'groep:2014',
			'kassa'				 => 'groep:2014',
			'nieuw'				 => 'groep:2014',
			'wijzig'			 => 'groep:2014',
			'aantal'			 => 'groep:2014',
			'geserveerd'		 => 'groep:2014',
			'serveerstatus'		 => 'groep:2014',
			'financienstatus'	 => 'groep:2014',
			'opmerking'			 => 'groep:2014',
		);
	}

	public function performAction(array $args = array()) {
		$this->action = 'nieuw';
		if ($this->hasParam(3)) {
			$this->action = $this->getParam(3);
		}
		switch ($this->action) {
			case 'aantal':
			case 'geserveerd':
			case 'serveerstatus':
			case 'financienstatus':
			case 'opmerking':

				if (!isset($_POST['id'][0])) {
					$this->geentoegang();
				}
				$id = filter_var($_POST['id'][0], FILTER_SANITIZE_NUMBER_INT);
				$bestelling = $this->model->getBestelling((int) $id);

				if (!$bestelling) {
					$this->geentoegang();
				}
				parent::performAction(array($bestelling));
				break;

			default:
				parent::performAction($this->getParams(4));
		}
	}

	public function overzicht($y = null, $m = null, $d = null) {
		if ($this->isPosted()) {
			$y = (int) $y;
			$m = (int) $m;
			$d = (int) $d;
			if (checkdate($m, $d, $y)) {
				$datum = $y . '-' . $m . '-' . $d;
				$data = $this->model->find('datum = ?', array($datum));
			} else {
				$data = $this->model->find();
			}
			$this->view = new HappieBestellingenData($data);
		} else {
			$body = new HappieBestellingenView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function serveer() {
		if ($this->isPosted()) {
			$data = $this->model->find('datum = ?', array(date('Y-m-d')));
			$this->view = new HappieBestellingenData($data);
		} else {
			$body = new HappieServeerView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function keuken() {
		if ($this->isPosted()) {
			$data = $this->model->find('datum = ?', array(date('Y-m-d')));
			$this->view = new HappieBestellingenData($data);
		} else {
			$body = new HappieKeukenView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function bar() {
		if ($this->isPosted()) {
			$data = $this->model->find('datum = ?', array(date('Y-m-d')));
			$this->view = new HappieBestellingenData($data);
		} else {
			$body = new HappieBarView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function kassa() {
		if ($this->isPosted()) {
			$data = $this->model->find('datum = ?', array(date('Y-m-d')));
			$this->view = new HappieBestellingenData($data);
		} else {
			$body = new HappieKassaView();
			$this->view = new CsrLayout3Page($body);
		}
	}

	public function nieuw() {
		$form = new HappieBestelForm();
		if ($this->isPosted() AND $form->validate()) {
			$bestellingen = array();
			$sum = 0;
			foreach ($form->getValues() as $item_id => $value) {
				if ($value['aantal'] > 0) {
					$bestellingen[] = $this->model->newBestelling($value['tafel'], $item_id, $value['aantal'], $value['opmerking']);
					$sum += $value['aantal'];
				}
			}
			setMelding('Totaal ' . $sum . ' dingen besteld voor tafel ' . $value['tafel'], 1);
			redirect(happieUrl . '/serveer');
		}
		$this->view = new CsrLayout3Page($form);
	}

	public function wijzig($id) {
		$bestelling = $this->model->getBestelling((int) $id);
		if (!$bestelling) {
			setMelding('Bestelling bestaat niet', -1);
			redirect(happieUrl);
		}
		$form = new HappieBestellingWijzigenForm($bestelling);
		if ($this->isPosted() AND $form->validate()) {
			$this->model->update($bestelling);
			setMelding('Wijziging succesvol opgeslagen', 1);
			redirect(happieUrl . '/overzicht');
		}
		$this->view = new CsrLayout3Page($form);
	}

	public function aantal(HappieBestelling $bestelling) {
		$aantal = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_NUMBER_INT);
		$bestelling->aantal = (int) $aantal;
		$this->model->update($bestelling);
		$this->view = new JsonResponse($bestelling->aantal);
	}

	public function geserveerd(HappieBestelling $bestelling) {
		$aantal = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_NUMBER_INT);
		$bestelling->aantal_geserveerd = (int) $aantal;
		$this->model->update($bestelling);
		$this->view = new JsonResponse($bestelling->aantal_geserveerd);
	}

	public function serveerstatus(HappieBestelling $bestelling) {
		$status = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
		$bestelling->serveer_status = $status;
		$this->model->update($bestelling);
		$this->view = new JsonResponse($bestelling->serveer_status);
	}

	public function financienstatus(HappieBestelling $bestelling) {
		$status = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
		$bestelling->financien_status = $status;
		$this->model->update($bestelling);
		$this->view = new JsonResponse($bestelling->financien_status);
	}

	public function opmerking(HappieBestelling $bestelling) {
		$opmerking = filter_input(INPUT_POST, 'value', FILTER_SANITIZE_STRING);
		$bestelling->opmerking = $opmerking;
		$this->model->update($bestelling);
		$this->view = new JsonResponse($bestelling->opmerking);
	}

}
