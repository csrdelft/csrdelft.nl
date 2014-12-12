<?php

require_once 'model/happie/BestellingenModel.class.php';

/**
 * HappieRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de Happietaria module.
 * 
 */
class HappieRouterController extends Controller {

	public function __construct($query) {
		parent::__construct($query, $query); // Use model to pass through query
	}

	public function performAction(array $args = array()) {
		$this->action = 'bestel';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$controller = parent::performAction(); // modifies action for routing
		define('happieUrl', '/happie/' . $this->action);
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
			case 'bestel':
			case 'menukaart':
			case 'menugroep':
				return LoginModel::mag('groep:2014');

			default:
				return false;
		}
	}

	public function bestel() {
		require_once 'controller/happie/BestellingenController.class.php';
		return new HappieBestellingenController($this->model);
	}

	public function menukaart() {
		require_once 'controller/happie/MenukaartItemsController.class.php';
		return new HappieMenukaartItemsController($this->model);
	}

	public function menugroep() {
		require_once 'controller/happie/MenukaartGroepenController.class.php';
		return new HappieMenukaartGroepenController($this->model);
	}

}
