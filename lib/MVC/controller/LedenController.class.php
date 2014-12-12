<?php

/**
 * LedenController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller voor de ledenlijst.
 */
class LedenController extends AclController {

	public function __construct($query) {
		parent::__construct($query, null);
		if (!$this->isPosted()) {
			$this->acl = array(
				'lijst'			 => 'P_OUDLEDEN_READ',
				'stamboom'		 => 'P_OUDLEDEN_READ',
				'verjaardagen'	 => 'P_LEDEN_READ'
			);
		} else {
			$this->acl = array();
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'lijst';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function lijst() {
		redirect(CSR_ROOT . '/ledenlijst');
	}

	public function stamboom($uid = null) {
		require_once 'MVC/view/StamboomView.class.php';
		$body = new StamboomView($uid);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('stamboom');
	}

	public function verjaardagen() {
		require_once 'MVC/view/VerjaardagenView.class.php';
		$body = new VerjaardagenView('alleverjaardagen');
		$this->view = new CsrLayoutPage($body);
	}

}
