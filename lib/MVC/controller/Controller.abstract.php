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
	 * @var mixed
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
	 * Broken down query to positional (REST) and named (KVP) parameters
	 * @var array
	 */
	private $queryparts;

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
				if (count($kvp) > 1) {
					$this->queryparts[$kvp[0]] = $kvp[1];
				} else {
					$this->queryparts[$kvp[0]] = $kvp[0];
				}
			}
		}
	}

	/**
	 * KVP: named parameters
	 * REST: positional parameters
	 * 
	 * @param string $key
	 * @return boolean
	 */
	protected function hasParam($key) {
		return array_key_exists($key, $this->queryparts) AND isset($this->queryparts[$key]) AND $this->queryparts[$key] !== ''; // don't use empty() because 0 is allowed
	}

	/**
	 * KVP: named parameters
	 * REST: positional parameters
	 * 
	 * @param string $key
	 * @return boolean
	 */
	protected function getParam($key) {
		return $this->queryparts[$key];
	}

	/**
	 * Return query paramter values from $num onwards.
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
	 * If request method is POST.
	 * 
	 * @return boolean
	 */
	public function isPosted() {
		return isPosted();
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

	abstract protected function mag($action, $resource);

	public function performAction(array $args = array()) {
		if (!$this->mag($this->action, $_SERVER['REQUEST_METHOD'])) {
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
			echo 'Access denied';
		} else {
			require_once 'MVC/model/CmsPaginaModel.class.php';
			require_once 'MVC/view/CmsPaginaView.class.php';
			$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('geentoegang'));
			$this->view = new CsrLayoutPage($body);
			$this->view->view();
		}
		exit;
	}

}
