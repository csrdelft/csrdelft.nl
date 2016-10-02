<?php

require_once 'model/betalen/FacturenModel.class.php';
require_once 'view/BetalenView.class.php';
require_once 'view/ReactAppView.class.php';

/**
 * BetalenController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class BetalenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'app'					 => 'P_STREPEN_IK',
				'page'					 => 'P_BETALEN_IK',
				'facturen'				 => 'P_BETALEN_IK',
				'factuuritems'			 => 'P_BETALEN_ADD',
				'klanten'				 => 'P_BETALEN_ADMIN',
				'producten'				 => 'P_BETALEN_ADD',
				'productcategorieen'	 => 'P_STREPEN_ADMIN',
				'productprijzen'		 => 'P_STREPEN_MOD',
				'productprijslijsten'	 => 'P_BETALEN_ADMIN',
				'streeplijsten'			 => 'P_STREPEN_IK',
				'streeplijstproducten'	 => 'P_STREPEN_ADD',
				'transacties'			 => 'P_BETALEN_IK',
			);
		} else {
			$this->acl = array(
				'facturen' => 'P_BETALEN_IK'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'page';
		}
		parent::performAction($this->getParams(3));
	}

	public function GET_app() {
		$body = new BetalenView(null);
		$this->view = new ReactAppView('react_example', $body, 'Betalen');
	}

	public function GET_page() {
		$body = new BetalenView(null);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('react');
		$this->view->addScript('https://unpkg.com/babel-core@5.8.38/browser.min.js', true);
	}

	public function GET_facturen() {
		if (LoginModel::mag('P_BETALEN_ADMIN')) {
			$facturen = FacturenModel::instance()->find();
		} else {
			$klant = KlantenModel::getKlant(LoginModel::getUid());
			$facturen = FacturenModel::instance()->find('klant_id = ?', array($klant->klant_id));
		}
		$this->view = new JsonLijstResponse($facturen);
	}

	public function POST_facturen($action = null) {
		$this->model = FacturenModel::instance();
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if ($action == 'nieuw' AND empty($selection)) {
			$factuur = $this->model->newFactuur(null, null);
			$form = new FactuurForm($factuur, $action);
			if ($form->validate()) {
				$this->model->create($factuur);
				$this->view = new DataTableResponse($factuur);
			} else {
				$this->view = $form;
			}
		} elseif ($action == 'wijzigen' AND sizeof($selection) == 1) {
			$factuur = reset($selection);
			$form = new FactuurForm($factuur, $action);
			if ($form->validate()) {
				$this->model->update($factuur);
				$this->view = new DataTableResponse($factuur);
			} else {
				$this->view = $form;
			}
		} elseif ($action == 'verwijderen' AND ! empty($selection)) {
			$verwijderd = array();
			foreach ($selection as $uuid) {
				if ($this->model->deleteByUUID($uuid) > 0) {
					$verwijderd[] = $uuid;
				}
			}
			$this->view = new RemoveRowsResponse($verwijderd);
		} else {
			return $this->geentoegang();
		}
	}

	public function GET_producten() {
		if (LoginModel::mag('P_BETALEN_ADMIN')) {
			$facturen = FacturenModel::instance()->find();
		} else {
			$klant = KlantenModel::getKlant(LoginModel::getUid());
			$facturen = FacturenModel::instance()->find('klant_id = ?', array($klant->klant_id));
		}
		$this->view = new JsonLijstResponse($facturen);
	}

	public function POST_producten($action = null) {
		$this->model = ProductenModel::instance();
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if ($action == 'nieuw' AND empty($selection)) {
			$item = $this->model->newProduct(null, null);
			$form = new ProductFrom($item, $action);
			if ($form->validate()) {
				$this->model->create($item);
				$this->view = new DataTableResponse($item);
			} else {
				$this->view = $form;
			}
		} elseif ($action == 'wijzigen' AND sizeof($selection) == 1) {
			$item = reset($selection);
			$form = new ProductForm($item, $action);
			if ($form->validate()) {
				$this->model->update($item);
				$this->view = new DataTableResponse($item);
			} else {
				$this->view = $form;
			}
		} elseif ($action == 'verwijderen' AND ! empty($selection)) {
			$verwijderd = array();
			foreach ($selection as $uuid) {
				if ($this->model->deleteByUUID($uuid) > 0) {
					$verwijderd[] = $uuid;
				}
			}
			$this->view = new RemoveRowsResponse($verwijderd);
		} else {
			return $this->geentoegang();
		}
	}

}
