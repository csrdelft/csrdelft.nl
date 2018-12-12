<?php

namespace CsrDelft\controller\framework;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\bbcode\CsrBB;
use CsrDelft\view\CsrLayoutPage;
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
	 * Query broken down to positional (REST) parameters
	 * @var array
	 */
	private $queryparts;
	/**
	 * Query broken down to named (KVP) parameters
	 * @var array
	 */
	private $kvp = array();

	public function __construct($query, $model, $methods = array('GET', 'POST')) {
		$this->model = $model;
		$this->methods = $methods;

		// split into REST and KVP query part
		$queryparts = explode('?', $query, 2);

		// parse REST query
		$this->queryparts = explode('/', $queryparts[0]);

		// parse KVP query
		if (count($queryparts) > 1) {

			// split into key-value-pairs
			$parts = explode('&', $queryparts[1]);
			foreach ($parts as $part) {

				// split key-value-pair
				$kvp = explode('=', $part, 2);
				$key = urldecode($kvp[0]);
				if (count($kvp) > 1) {
					$this->kvp[$key] = urldecode($kvp[1]);
				} else {
					$this->kvp[$key] = $key;
				}
			}
		}
	}

	/**
	 * REST: positional parameters
	 * KVP: named parameters
	 *
	 * @param string|int $key
	 * @return boolean
	 */
	protected function hasParam($key) {
		// don't use empty() because 0 is allowed
		if (isset($this->queryparts[$key]) AND $this->queryparts[$key] !== '') {
			return true;
		} elseif (isset($this->kvp[$key]) AND $this->kvp[$key] !== '') {
			return true;
		}
		return false;
	}

	/**
	 * REST: positional parameters
	 * KVP: named parameters
	 *
	 * @param string|int $key
	 * @return string
	 */
	protected function getParam($key) {
		if (array_key_exists($key, $this->kvp)) {
			return $this->kvp[$key];
		}
		return $this->queryparts[$key];
	}

	/**
	 * Return GET query params.
	 *
	 * @return string[]
	 */
	protected function getQueryParams() {
		return $this->kvp;
	}

	/**
	 * Return REST query paramter values from $num onwards.
	 *
	 * @param int $num skip params before this
	 * @return array
	 */
	protected function getParams($num = 0) {
		$params = array_values($this->queryparts);
		for ($i = 0; $i < $num; $i++) {
			if (isset($params[$i])) {
				unset($params[$i]);
			}
		}
		return $params;
	}

	public function getMethod() {
		return $_SERVER['REQUEST_METHOD'];
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
		$this->view = new CsrLayoutPage($body);
		$this->view->view();
		exit;
	}

}
