<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\ketzertovenaar\KetzerTovenaarView;

/**
 * KetzerTovenaar.class.php
 *
 * @author J.P.T. Nederveen <ik@tim365.nl>
 */
class KetzerTovenaarController extends AclController {
	public function __construct($query) {
		parent::__construct($query, null);

		if ($this->getMethod() == 'GET') {
			$this->acl = [
				'nieuw' => 'P_LEDEN_READ',
			];
		} else {
			$this->acl = [

			];
		}
	}

	public function performAction(array $args = array()) {
		// Standaardactie
		$this->action = 'nieuw';

		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}

		parent::performAction($args);
	}

	public function nieuw() {
		$this->view = new KetzerTovenaarView();
		$this->view = new CsrLayoutPage($this->view);
	}
}
