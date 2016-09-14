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
				'app'					 => 'P_ADMIN',
				'page'					 => 'P_ADMIN',
				'facturen'				 => 'P_ADMIN',
				'factuuritems'			 => 'P_ADMIN',
				'klanten'				 => 'P_ADMIN',
				'producten'				 => 'P_ADMIN',
				'productcategorieen'	 => 'P_ADMIN',
				'productprijzen'		 => 'P_ADMIN',
				'productprijslijsten'	 => 'P_ADMIN',
				'streeplijsten'			 => 'P_ADMIN',
				'streeplijstproducten'	 => 'P_ADMIN',
				'transacties'			 => 'P_ADMIN',
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
		$facturen = FacturenModel::instance()->find();
		$this->view = new JsonLijstResponse($facturen);
	}

}
