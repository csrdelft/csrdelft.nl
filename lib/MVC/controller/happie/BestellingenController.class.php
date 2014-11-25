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
			case 'overzicht':
			case 'serveer':
			case 'keuken':
			case 'bar':
			case 'kassa':
				parent::performAction($this->getParams(4)); // set view body
				if ($this->isPosted()) {
					$this->view = new HappieBestellingenData($this->view);
				} else {
					$this->view = new CsrLayout3Page($this->view);
				}
				break;

			case 'wijzig':
			case 'aantal':
			case 'geserveerd':
			case 'serveerstatus':
			case 'financienstatus':
			case 'opmerking':

				if (!$this->isPosted()) {
					$this->geentoegang();
				}
				$field = new ObjectIdField(new HappieBestelling());
				if ($field->validate()) {
					$ids = $field->getValue();
					$bestelling = $this->model->getBestelling((int) $ids[0]);
					if (!$bestelling) {
						throw new Exception('Bestelling bestaat niet');
					}
				} else {
					throw new Exception('Missing objectId');
				}
				parent::performAction(array($bestelling)); // set view form

				if ($this->view->validate()) {
					$this->model->update($bestelling);
					$this->view = new HappieBestellingenData(array($bestelling));
				}
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
				$this->view = $this->model->find('datum = ?', array($datum));
			} else {
				$this->view = $this->model->find();
			}
		} else {
			$this->view = new HappieBestellingenView();
		}
	}

	public function serveer() {
		if ($this->isPosted()) {
			$this->view = $this->model->find('datum = ?', array(date('Y-m-d')));
		} else {
			$this->view = new HappieServeerView();
		}
	}

	public function keuken() {
		if ($this->isPosted()) {
			$this->view = $this->model->find('datum = ?', array(date('Y-m-d')));
		} else {
			$this->view = new HappieKeukenView();
		}
	}

	public function bar() {
		if ($this->isPosted()) {
			$this->view = $this->model->find('datum = ?', array(date('Y-m-d')));
		} else {
			$this->view = new HappieBarView();
		}
	}

	public function kassa() {
		if ($this->isPosted()) {
			$this->view = $this->model->find('datum = ?', array(date('Y-m-d')));
		} else {
			$this->view = new HappieKassaView();
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

	public function wijzig(HappieBestelling $bestelling) {
		$this->view = new HappieBestellingWijzigenForm($bestelling);
	}

	public function aantal(HappieBestelling $bestelling) {
		$field = new IntField('aantal', $bestelling->aantal, null);
		$this->view = new InlineForm($bestelling, 'aantal' . $bestelling->bestelling_id, happieUrl . '/aantal', $field);
	}

	public function geserveerd(HappieBestelling $bestelling) {
		$field = new IntField('aantal_geserveerd', $bestelling->aantal_geserveerd, null);
		$this->view = new InlineForm($bestelling, 'geserveerd' . $bestelling->bestelling_id, happieUrl . '/geserveerd', $field);
	}

	public function serveerstatus(HappieBestelling $bestelling) {
		$field = new SelectField('serveer_status', $bestelling->serveer_status, null, HappieServeerStatus::getSelectOptions());
		$this->view = new InlineForm($bestelling, 'serveerstatus' . $bestelling->bestelling_id, happieUrl . '/serveerstatus', $field);
	}

	public function financienstatus(HappieBestelling $bestelling) {
		$field = new SelectField('financien_status', $bestelling->financien_status, null, HappieFinancienStatus::getSelectOptions());
		$this->view = new InlineForm($bestelling, 'financienstatus' . $bestelling->bestelling_id, happieUrl . '/financienstatus', $field);
	}

	public function opmerking(HappieBestelling $bestelling) {
		$field = new TextareaField('opmerking', $bestelling->opmerking, 'Allergie/Opmerking');
		$this->view = new InlineForm($bestelling, 'opmerking' . $bestelling->bestelling_id, happieUrl . '/opmerking', $field);
	}

}
