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
				'overzicht' => 'groep:2014'
			);
		} else {
			$this->acl = array(
				'nieuw'		 => 'groep:2014',
				'wijzig'	 => 'groep:2014',
				'verwijder'	 => 'groep:2014:HMT penningmeester'
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

	public function overzicht() {
		$body = new HappieBestellingenView();
		$this->view = new CsrLayout3Page($body);
	}

	public function nieuw() {
		
	}

	public function wijzig() {
		
	}

	public function verwijder() {
		
	}

}
