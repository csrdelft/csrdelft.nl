<?php

/**
 * Controller.abstract.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een controller zorgt ervoor dat de juiste acties uitgevoerd worden,
 * dat er gecontroleerd wordt of de gebruiker mag doen dat hij probeert
 * te doen, en dat er een View aangemaakt wordt.
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
	 * Query broken down to positional (REST) parameters
	 * @var array
	 */
	private $queryparts;
	/**
	 * Query broken down to named (KVP) parameters
	 * @var array
	 */
	private $kvp = array();

	public function __construct($query, $model) {
		$this->model = $model;

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
	 * @param string $key
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
	 * @param string $key
	 * @return boolean
	 */
	protected function getParam($key) {
		if (array_key_exists($key, $this->kvp)) {
			return $this->kvp[$key];
		}
		return $this->queryparts[$key];
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

	/**
	 * Is the current request posted?
	 * @return boolean
	 */
	public function isPosted() {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
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

	abstract protected function mag($action, array $args);

	public function performAction(array $args = array()) {
		// Controleer of er een ban is ingesteld
		$account = LoginModel::getAccount();
		if (isset($account->blocked_reason)) {
			setMelding(CsrBB::parse($account->blocked_reason), -1);
			$this->action = 'geentoegang';
		}
		// Controleer of de actie uitgevoerd mag worden met de gegeven argumenten
		elseif (!$this->mag($this->action, $args)) {
			//DebugLogModel::instance()->log(get_class($this), $this->action, $args, 'geentoegang');
			$this->action = 'geentoegang';
		}
		if (!$this->hasAction($this->action)) {
			throw new Exception('Action undefined: ' . $this->action);
		}
		return call_user_func_array(array($this, $this->action), $args);
	}

	protected function geentoegang() {
		http_response_code(403);
		if ($this->isPosted()) {
			die('403 Forbidden');
		}
		// Redirect to login form
		elseif (LoginModel::getUid() === 'x999') {
			setcookie('goback', REQUEST_URI, time() + (int) Instellingen::get('beveiliging', 'session_lifetime_seconds'), '/', CSR_DOMAIN, FORCE_HTTPS, true);
			redirect(CSR_ROOT);
		}
		// GUI 403
		else {
			require_once 'model/CmsPaginaModel.class.php';
			require_once 'view/CmsPaginaView.class.php';
			$body = new CmsPaginaView(CmsPaginaModel::get('geentoegang'));
			$this->view = new CsrLayoutPage($body);
			$this->view->view();
		}
		exit;
	}

}
