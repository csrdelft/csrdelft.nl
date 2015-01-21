<?php

require_once 'controller/groepen/GroepenController.class.php';
require_once 'model/GroepenModel.class.php';
require_once 'view/groepen/GroepenView.class.php';

/**
 * GroepenRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de groepen module.
 */
class GroepenRouterController extends Controller {

	public function __construct($query) {
		$query = str_replace('/overig', '/groepen', $query);
		parent::__construct($query, $query); // use model to pass through query
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		} else {
			$this->action = 'ketzers'; // default
		}
		$class = ucfirst($this->action) . 'Controller';

		require_once 'controller/groepen/' . $class . '.class.php';
		$controller = new $class($this->model); // query
		$controller->performAction();

		define('groepenUrl', '/groepen/' . $this->action);
		$this->view = $controller->getView();
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $method) {
		switch ($action) {
			// groep
			case 'groepen':
			case 'onderverenigingen':
			case 'woonoorden':
			case 'lichtingen':
			case 'verticalen':
			// opvolgbare groep
			case 'kringen':
			case 'werkgroepen':
			case 'commissies':
			case 'besturen':
			case 'ketzers':
			case 'activiteiten':
			case 'conferenties':
				return LoginModel::mag('P_LEDEN_READ');

			default:
				return false;
		}
	}

}
