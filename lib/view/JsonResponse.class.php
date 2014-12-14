<?php

require_once 'view/View.interface.php';

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

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return null;
	}

}

class FlotDataResponse extends JsonResponse {

	public function getJson($data) {
		$array = array();
		foreach ($data as $key => $entry) {
			if (is_int($key)) {
				$array[$key] = (int) $entry;
			}
		}
		return json_encode($array);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '[' . "\n";
		$comma = false;
		foreach ($this->model as $data) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			echo $this->getJson($data);
		}
		echo "\n]";
	}

}
