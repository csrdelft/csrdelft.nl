<?php

namespace CsrDelft\view;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class JsonResponseV2 implements View {

	protected $model;
	protected $code;
	protected $meta;

	public function __construct(\JsonSerializable $model, $meta = [], $code = 200) {
		$this->model = $model;
		$this->code = $code;
		$this->meta = $meta;
	}

	public function getMeta() {
		return $this->meta;
	}

	public function getJson() {
		return array_merge(
			$this->getMeta(),
			['data' => $this->model]
		);
	}

	public function view() {
		if ($_SERVER['REQUEST_METHOD'] === 'GET') throw new \Exception('Unsafe method');

		http_response_code($this->code);
		header('Content-Type: application/json');
		echo json_encode($this->getJson());
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
