<?php

require_once 'model/CommissieVoorkeurenModel.class.php';
require_once 'view/CommissieVoorkeurenView.class.php';

/**
 * CommissieVoorkeurenController.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor commissie voorkeuren.
 */
class CommissieVoorkeurenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'overzicht'	 => 'groep:bestuur,1137',
				'lidpagina'	 => 'groep:bestuur,1137'
			);
		} else {
			$this->acl = array(
				'lidpagina' => 'groep:bestuur,1137'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'overzicht';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function overzicht($commissieId = -1) {
		$body = new CommissieVoorkeurenView($commissieId);
		$this->view = new CsrLayoutPage($body);
	}

	public function lidpagina($uid = -1) {
		if (!ProfielModel::existsUid($uid)) {
			$this->geentoegang();
		}
		if (isset($_POST['opmerkingen'])) {
			$voorkeur = new CommissieVoorkeurenModel($uid);
			$voorkeur->setPraesesOpmerking(filter_input(INPUT_POST, 'opmerkingen', FILTER_SANITIZE_STRING));
		}
		$body = new CommissieVoorkeurenProfiel($uid);
		$this->view = new CsrLayoutPage($body);
	}

}
