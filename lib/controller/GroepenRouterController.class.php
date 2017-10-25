<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\Controller;
use CsrDelft\model\security\LoginModel;


/**
 * GroepenRouterController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Router voor de groepen module.
 */
class GroepenRouterController extends Controller {

	public function __construct($query) {
		parent::__construct($query, null);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$class = $this->getParam(2);
			if ($class === 'overig') {
				$class = 'rechtengroepen';
			}
		} else {
			$class = 'commissies'; // default
		}
		$class = strtolower($class);
		if (!$this->mag($class, array())) {
			$this->exit_http(403);
		}
		$class = ucfirst($class) . 'Controller';
		$namespacedClass = 'CsrDelft\\controller\\groepen\\' . $class;
		$controller = new $namespacedClass(REQUEST_URI);
		$controller->performAction();

		$this->view = $controller->getView();
	}

	/**
	 * Check permissions & valid params in sub-controller.
	 *
	 * @return boolean
	 */
	protected function mag($action, array $args) {
		switch ($action) {
			case 'activiteiten':
			case 'besturen':
			case 'commissies':
			case 'rechtengroepen':
			case 'ketzers':
			case 'onderverenigingen':
			case 'werkgroepen':
			case 'woonoorden':
			case 'lichtingen':
			case 'verticalen':
			case 'kringen':
				return LoginModel::mag('P_LOGGED_IN');

			default:
				return false;
		}
	}

}
