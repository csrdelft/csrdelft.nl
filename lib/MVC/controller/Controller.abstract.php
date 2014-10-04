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
	 * Broken down query to positional (REST) or named (KVP) parameters
	 * @var array
	 */
	private $queryparts;
	/**
	 * Is this controller called with a server request query containing
	 * key-value-pair (KVP) or only representational state transfer (REST)
	 * @var boolean
	 */
	private $kvp;

	public function __construct($query, $model) {
		$this->model = $model;
		// split at ?-mark
		$mark = strpos($query, '?');
		if ($mark !== false) {
			$this->kvp = true;
			// parse REST query
			$this->queryparts = explode('/', substr($query, 0, $mark));
			// parse KVP query
			$parts = explode('&', substr($query, $mark));
			foreach ($parts as $key => $value) {
				$this->queryparts[$key] = explode('=', $value);
			}
		} else {
			$this->kvp = false;
			// parse REST query
			$this->queryparts = explode('/', $query);
		}
	}

	/**
	 * Is this controller called with a server request query containing
	 * key-value-pair (KVP) or only representational state transfer (REST)
	 * @return boolean
	 */
	public function hasKvp() {
		return $this->kvp;
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

	abstract protected function mag($action);

	public function performAction(array $args = array()) {
		if (!$this->mag($this->action)) {
			//DebugLogModel::instance()->log(get_called_class(), $this->action, $args, 'geentoegang');
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
			echo 'Forbidden';
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
