<?php

namespace CsrDelft\controller\framework;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\service\CsrfService;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\View;

/**
 * Controller.abstract.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * De controller bepaalt welke actie er uitgevoerd moet worden aan de hand van
 * de request method en query.
 * Ook dient de controller te controleren of de gebruiker geauthorizeerd is.
 * Tenslotte moet er een View aangemaakt worden door de actie.
 *
 */
abstract class Controller {
	use QueryParamTrait;
	/**
	 * Data model
	 * @var PersistenceModel
	 */
	protected $model;
	/**
	 * The view to be shown
	 * @var View
	 */
	protected $view;
	/**
	 * Action to be performed
	 * @var string
	 */
	protected $action;
	/**
	 * Allowed request methods
	 * @var array
	 */
	protected $methods;
	/**
	 * Use this to list all actions for which csrf protection should not be applied.
	 * For example:
	 * $csrfUnsafe = ['POST'=>['bewerken']]
	 * @var array
	 */
	protected $csrfUnsafe = [];
	private $query;

	public function __construct($query, $model, $methods = array('GET', 'POST')) {
		$this->model = $model;
		$this->methods = $methods;
		$this->query = $query;
	}

	public function getModel() {
		return $this->model;
	}

	public function getView() {
		return $this->view;
	}

	public function getAction() {
		return $this->action;
	}

	/**
	 * If named action is defined.
	 *
	 * @param string $action
	 * @return boolean
	 */
	public function hasAction($action) {
		return method_exists($this, $action);
	}

	/**
	 * Check of een bepaalde actie aangeroepen kan worden.
	 *
	 * @param string $action
	 * @param mixed[] $args
	 * @return bool Of de actie kan worden aangeroepen.
	 */
	protected function canCallAction(string $action, array $args) {
		try {
			$args = array_values($args);
			$method = new \ReflectionMethod(get_class($this), $action);
			$parameters = $method->getParameters();

			$requiredParams = array_filter($parameters, function(\ReflectionParameter $param) { return !$param->isOptional();});

			// Als het aantal parameters kleiner is dan het aantal verplichte parameters wordt er een TypeError gegooid.
			if (count($args) < count($requiredParams)) {
				return false;
			}

			for ($i = 0; $i < count($parameters); $i++) {
				$parameter = $parameters[$i];
				$arg = isset($args[$i]) ? $args[$i] : null;

				if ($arg == null && $parameter->isOptional()) {
					continue;
				}

				switch ($parameter->getType()) {
					case 'int':
						// Als de waarde stilletjes naar een int geconverteerd kan worden gaat het systeem akkoord.
						if (!is_numeric($arg)) return false;
						break;
					default:
						break;
				}
			}

			return true;

		} catch (\ReflectionException $ex) {
			throw new CsrException('canCallAction gefaalt', 0, $ex);
		}
	}

	abstract protected function mag($action, array $args);

	/**
	 * @param array $args
	 * @return mixed
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function performAction(array $args = array()) {
		if (!$this->csrfUnsafeAllowed($this->getMethod(), $this->action)) {
			CsrfService::preventCsrf();
		}

		// Controleer of er een ban is ingesteld
		$account = LoginModel::getAccount();
		if (isset($account->blocked_reason)) {
			setMelding(CsrBB::parse($account->blocked_reason), -1);
			$this->exit_http(403);
		}
		// Controleer of de request method toegestaan is
		if (!in_array($this->getMethod(), $this->methods)) {
			$this->exit_http(403);
		}
		// Controleer of de actie uitgevoerd mag worden met de gegeven argumenten
		if (!$this->mag($this->action, $args)) {
			//DebugLogModel::instance()->log(get_class($this), $this->action, $args, 'geentoegang');
			$this->exit_http(403);
		}
		// Specific action function is preferred
		$action = $this->getMethod() . '_' . $this->action;
		if ($this->hasAction($action)) {
			$this->action = $action;
		} // Controleer of de actie bestaat
		elseif (!$this->hasAction($this->action)) {
			throw new CsrException('Action undefined: ' . $this->action);
		} // Controleer of de actie aangeroepen kan worden
		elseif (!$this->canCallAction($this->action, $args)) {
			throw new CsrToegangException('Pagina niet gevonden', 404);
		}
		return call_user_func_array(array($this, $this->action), $args);
	}

	protected function exit_http($response_code) {
		http_response_code($response_code);
		if ($this->getMethod() == 'POST') {
			die($response_code);
		} // Redirect to login form
		elseif (LoginModel::getUid() === 'x999') {
			redirect_via_login(REQUEST_URI);
		}
		// GUI 403
		$body = new CmsPaginaView(CmsPaginaModel::get($response_code));
		$this->view = view('default', ['content' => $body]);
		$this->view->view();
		exit;
	}

	private function csrfUnsafeAllowed($method, $action) {
		return isset($this->csrfUnsafe[$method][$action]);
	}
}
