<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\ProfielService;
use CsrDelft\view\ledenlijst\LedenLijstResponse;
use CsrDelft\view\ledenlijst\LedenLijstTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 19/09/2018
 */
class LedenLijstController extends AclController {
	public function __construct($query) {
		parent::__construct($query, ProfielService::instance());

		$this->acl = [
			'lijst' => 'P_LOGGED_IN'
		];
	}

	public function performAction(array $args = array()) {
		$this->action = 'lijst';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->view = parent::performAction($this->getParams(3));
	}

	public function GET_lijst() {
		return view('ledenlijst.lijst', [
			'lijstTable' => new LedenLijstTable()
		]);
	}

	public function POST_lijst() {
		return new LedenLijstResponse(ProfielService::instance());
	}
}
