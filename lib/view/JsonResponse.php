<?php

namespace CsrDelft\view;


/**
 * JsonResponse.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class JsonResponse implements View {

	protected $model;
	protected $code;

	public function __construct($model, $code = 200) {
		$this->model = $model;
		$this->code = $code;
	}

	public function getJson($entity) {
		return json_encode($entity);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo $this->getJson($this->model);
	}

	public function getModel() {
		return $this->model;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return null;
	}

}
