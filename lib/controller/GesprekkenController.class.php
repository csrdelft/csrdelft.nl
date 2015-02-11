<?php

require_once 'model/GesprekkenModel.class.php';
require_once 'view/GesprekkenView.class.php';

/**
 * GesprekkenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de chat-functie.
 */
class GesprekkenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, GesprekkenModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'view' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'gesprekken' => 'P_LOGGED_IN',
				'zeg'		 => 'P_LOGGED_IN',
				'lees'		 => 'P_LOGGED_IN',
				'toevoegen'	 => 'P_LOGGED_IN',
				'sluiten'	 => 'P_LOGGED_IN',
			);
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
		$body = new GesprekkenView($this->model);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('gesprekken');
	}

	public function gesprekken() {
		//TODO
	}

	public function zeg() {
		//TODO
	}

	public function lees() {
		//TODO
	}

	public function toevoegen() {
		//TODO
	}

	public function sluiten() {
		//TODO
	}

}
