<?php

require_once 'MVC/controller/groepen/GroepenController.abstract.php';
require_once 'MVC/model/GroepenModel.class.php';
require_once 'MVC/view/groepen/GroepenView.class.php';

/**
 * GroepenRouterController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Router voor de groepen module.
 */
class GroepenRouterController extends Controller {

	/**
	 * Pass through query
	 * @var string
	 */
	private $query;

	public function __construct($query) {
		parent::__construct($query);
		$this->query = $query;
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$controller = parent::performAction(); // modifies action (default)
		Instellingen::setTemp('groepen', 'url', '/groepen/' . $this->action);
		$controller->performAction();
		$this->view = $controller->getContent();
	}

	/**
	 * Check permissions & valid params in actions.
	 * 
	 * @return boolean
	 */
	protected function hasPermission() {
		switch ($this->action) {
			case 'commissies':
			case 'besturen':
			case 'sjaarcies':
			case 'woonoorden':
			case 'werkgroepen':
			case 'onderverenigingen':
			case 'ketzers':
			case 'activiteiten':
			case 'conferenties':
				return true;

			default:
				$this->action = 'commissies';
				return true;
		}
	}

	public function commissies() {
		require_once 'MVC/controller/groepen/CommissiesController.class.php';
		return new CommissiesController($this->query);
	}

	public function besturen() {
		require_once 'MVC/controller/groepen/BesturenController.class.php';
		return new BesturenController($this->query);
	}

	public function sjaarcies() {
		require_once 'MVC/controller/groepen/SjaarciesController.class.php';
		return new SjaarciesController($this->query);
	}

	public function woonoorden() {
		require_once 'MVC/controller/groepen/WoonoordenController.class.php';
		return new WoonoordenController($this->query);
	}

	public function werkgroepen() {
		require_once 'MVC/controller/groepen/WerkgroepenController.class.php';
		return new WerkgroepenController($this->query);
	}

	public function onderverenigingen() {
		require_once 'MVC/controller/groepen/OnderverenigingenController.class.php';
		return new OnderverenigingenController($this->query);
	}

	public function ketzers() {
		require_once 'MVC/controller/groepen/KetzersController.class.php';
		return new KetzersController($this->query);
	}

	public function activiteiten() {
		require_once 'MVC/controller/groepen/ActiviteitenController.class.php';
		return new ActiviteitenController($this->query);
	}

	public function conferenties() {
		require_once 'MVC/controller/groepen/ConferentiesController.class.php';
		return new ConferentiesController($this->query);
	}

}
