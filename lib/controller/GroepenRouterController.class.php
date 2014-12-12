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
		parent::__construct($query, $query); // Use model to pass through query
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$controller = parent::performAction(); // modifies action (default)
		define('groepenUrl', '/groepen/' . $this->action);
		$controller->performAction();
		$this->view = $controller->getView();
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function mag($action, $resource) {
		switch ($action) {
			case 'commissies':
			case 'besturen':
			case 'sjaarcies':
			case 'woonoorden':
			case 'werkgroepen':
			case 'onderverenigingen':
			case 'ketzers':
			case 'activiteiten':
			case 'conferenties':
				return LoginModel::mag('P_LEDEN_READ');

			default:
				return false;
		}
	}

	public function commissies() {
		require_once 'controller/groepen/CommissiesController.class.php';
		return new CommissiesController($this->model);
	}

	public function besturen() {
		require_once 'controller/groepen/BesturenController.class.php';
		return new BesturenController($this->model);
	}

	public function sjaarcies() {
		require_once 'controller/groepen/SjaarciesController.class.php';
		return new SjaarciesController($this->model);
	}

	public function woonoorden() {
		require_once 'controller/groepen/WoonoordenController.class.php';
		return new WoonoordenController($this->model);
	}

	public function werkgroepen() {
		require_once 'controller/groepen/WerkgroepenController.class.php';
		return new WerkgroepenController($this->model);
	}

	public function onderverenigingen() {
		require_once 'controller/groepen/OnderverenigingenController.class.php';
		return new OnderverenigingenController($this->model);
	}

	public function ketzers() {
		require_once 'controller/groepen/KetzersController.class.php';
		return new KetzersController($this->model);
	}

	public function activiteiten() {
		require_once 'controller/groepen/ActiviteitenController.class.php';
		return new ActiviteitenController($this->model);
	}

	public function conferenties() {
		require_once 'controller/groepen/ConferentiesController.class.php';
		return new ConferentiesController($this->model);
	}

}
