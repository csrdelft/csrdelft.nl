<?php

require_once 'model/GroepenModel.abstract.php';
require_once 'controller/groepen/GroepenController.abstract.php';

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
			return $this->geentoegang();
		}
		$class = ucfirst($class) . 'Controller';

		require_once 'controller/groepen/' . $class . '.class.php';
		$controller = new $class(REQUEST_URI);
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
