<?php

namespace CsrDelft\controller;

use CsrDelft\controller\framework\Controller;
use CsrDelft\controller\groepen\AbstractGroepenController;
use CsrDelft\controller\groepen\ActiviteitenController;
use CsrDelft\controller\groepen\BesturenController;
use CsrDelft\controller\groepen\CommissiesController;
use CsrDelft\controller\groepen\KetzersController;
use CsrDelft\controller\groepen\KringenController;
use CsrDelft\controller\groepen\LichtingenController;
use CsrDelft\controller\groepen\OnderverenigingenController;
use CsrDelft\controller\groepen\RechtengroepenController;
use CsrDelft\controller\groepen\VerticalenController;
use CsrDelft\controller\groepen\WerkgroepenController;
use CsrDelft\controller\groepen\WoonoordenController;
use CsrDelft\model\security\LoginModel;


/**
 * GroepenRouterController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Router voor de groepen module.
 */
class GroepenRouterController extends Controller {

	/**
	 * @var array<string, string>
	 */
	protected static $groepSoorten = [
		'activiteiten' => ActiviteitenController::class,
		'besturen' => BesturenController::class,
		'commissies' => CommissiesController::class,
		'ketzers' => KetzersController::class,
		'kringen' => KringenController::class,
		'lichtingen' => LichtingenController::class,
		'onderverenigingen' => OnderverenigingenController::class,
		'rechtengroepen' => RechtengroepenController::class,
		'verticalen' => VerticalenController::class,
		'werkgroepen' => WerkgroepenController::class,
		'woonoorden' => WoonoordenController::class,
	];

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

		$class = static::$groepSoorten[$class];
		/** @var AbstractGroepenController $controller */
		$controller = new $class(REQUEST_URI);
		$controller->performAction();

		$this->view = $controller->getView();
	}

	/**
	 * Check permissions & valid params in sub-controller.
	 *
	 * @param $action
	 * @param array $args
	 * @return boolean
	 */
	protected function mag($action, array $args) {
		return isset(static::$groepSoorten[$action]);
	}
}
