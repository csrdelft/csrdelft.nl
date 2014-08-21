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

	public function __construct($model) {
		$this->model = $model;
	}

	public function view() {
		echo json_encode($this->model);
	}

	public function getModel() {
		return $this->model;
	}

}
