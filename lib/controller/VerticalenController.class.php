<?php

require_once 'model/GroepenModel.class.php';
require_once 'view/VerticalenView.class.php';

/**
 * VerticalenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor verticalen.
 */
class VerticalenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, VerticalenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'view'	 => 'P_LEDEN_READ',
				'emails' => 'P_LEDEN_READ',
				'zoeken' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'view';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function view() {
		$body = new VerticalenView($this->model->find());
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('verticalen');
	}

	public function emails($vertkring) {
		$this->view = new VerticaleEmailsView($vertkring);
	}

	public function zoeken() {
		if (!$this->hasParam('q')) {
			$this->geentoegang();
		}
		$zoekterm = '%' . $this->getParam('q') . '%';
		$limit = 5;
		if ($this->hasParam('limit')) {
			$limit = (int) $this->getParam('limit');
		}
		$result = array();
		foreach ($this->model->find('naam LIKE ?', array($zoekterm), null, null, $limit) as $verticale) {
			$result[] = array(
				'value' => 'verticale:' . $verticale->letter
			);
		}
		$this->view = new JsonResponse($result);
	}

}
