<?php
namespace CsrDelft\controller;

use CsrDelft\controller\framework\AclController;
use CsrDelft\model\BijbelroosterModel;
use CsrDelft\view\BijbelroosterView;
use CsrDelft\view\CsrLayoutPage;


/**
 * BijbelroosterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van het bijbelrooster.
 */
class BijbelroosterController extends AclController {

	public function __construct($query) {
		parent::__construct($query, BijbelroosterModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'bekijken' => 'P_PUBLIC'
			);
		} else {
			$this->acl = array(
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'bekijken';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function bekijken() {
		$rooster = $this->model->find();
		$body = new BijbelroosterView($rooster);
		$this->view = new CsrLayoutPage($body);
	}

}
