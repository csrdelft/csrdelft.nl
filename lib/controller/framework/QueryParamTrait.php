<?php


namespace CsrDelft\controller\framework;

/**
 * Hulpdingen om query params uit te lezen.
 *
 * @package CsrDelft\controller\framework
 */
trait QueryParamTrait {
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
	private $postVariables;

	private $initialized = false;

	private function init() {
		$this->initialized = true;
		// split into REST and KVP query part
		$queryparts = explode('?', REQUEST_URI, 2);

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

		// Filter input kan er niet mee overweg als de body in json formaat is axios stuurt standaard in json formaat.
		$filter_input = filter_input(INPUT_SERVER, 'CONTENT_TYPE', FILTER_SANITIZE_STRING);
		if (startsWith($filter_input, 'application/json')) {
			$body = json_decode(file_get_contents('php://input'), true);
			$this->postVariables = $body;
		}
	}

	protected function getPost($key) {
		if (!$this->initialized) $this->init();

		if (isset($this->postVariables[$key])) {
			return $this->postVariables[$key];
		}

		return filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
	}

	/**
	 * REST: positional parameters
	 * KVP: named parameters
	 *
	 * @param string|int $key
	 * @return boolean
	 */
	protected function hasParam($key) {
		if (!$this->initialized) $this->init();

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
		if (!$this->initialized) $this->init();

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
		if (!$this->initialized) $this->init();

		return $this->kvp;
	}

	/**
	 * Return REST query paramter values from $num onwards.
	 *
	 * @param int $num skip params before this
	 * @return array
	 */
	protected function getParams($num = 0) {
		if (!$this->initialized) $this->init();

		$params = array_values($this->queryparts);
		for ($i = 0; $i < $num; $i++) {
			if (isset($params[$i])) {
				unset($params[$i]);
			}
		}
		return $params;
	}
}