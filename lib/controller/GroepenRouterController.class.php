<?php

require_once 'model/GroepenModel.class.php';
require_once 'controller/groepen/OpvolgbareGroepenController.abstract.php';

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
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'commissies'; // default
		}
		if (!$this->mag($this->action, null)) {
			$this->geentoegang();
		}
		define('groepenUrl', '/groep/' . $this->action . '/');

		if ($this->action === 'overig') {
			$this->action = 'groepen';
		}
		$class = ucfirst($this->action) . 'Controller';

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
	protected function mag($action, $method) {
		switch ($action) {
			// groep
			case 'overig':
			case 'ketzers':
			case 'onderverenigingen':
			case 'woonoorden':
			case 'lichtingen':
			case 'verticalen':
			// opvolgbare groep
			case 'kringen':
			case 'werkgroepen':
			case 'commissies':
			case 'besturen':
			case 'activiteiten':
				return LoginModel::mag('P_LEDEN_READ');

			default:
				return false;
		}
	}

}
