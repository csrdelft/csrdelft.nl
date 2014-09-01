<?php

require_once 'MVC/view/View.interface.php';

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

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo json_encode($this->model);
	}

	public function getModel() {
		return $this->model;
	}

	public function getTitel() {
		return null;
	}

}
