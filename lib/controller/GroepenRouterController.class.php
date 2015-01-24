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
			$class = $this->getParam(2);
			if ($class === 'overig') {
				$class = 'groepen';
			}
		} else {
			$class = 'commissies'; // default
		}
		$class = strtolower($class);
		if (!$this->mag($class, array())) {
			$this->geentoegang();
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
			// groep
			case 'groepen':
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
