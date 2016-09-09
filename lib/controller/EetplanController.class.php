<?php

require_once 'model/EetplanModel.class.php';
require_once 'view/EetplanView.class.php';

/**
 * EetplanController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor eetplan.
 */
class EetplanController extends AclController {

	public function __construct($query) {
		parent::__construct($query, new EetplanModel());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'view'	 => 'P_LEDEN_READ',
				'noviet' => 'P_LEDEN_READ',
				'huis'	 => 'P_LEDEN_READ'
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
		$body = new EetplanView($this->model);
		$this->view = new CsrLayoutPage($body);
	}

	public function noviet($uid = null) {
		$body = new EetplanNovietView($this->model, $uid);
		$this->view = new CsrLayoutPage($body);
	}

	public function huis($id = null) {
		$body = new EetplanHuisView($this->model, $id);
		$this->view = new CsrLayoutPage($body);
	}

}
