<?php

namespace CsrDelft\view;


use Symfony\Component\HttpFoundation\Response;

/**
 * JsonResponse.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class JsonResponse extends Response {

	protected $model;

	public function __construct($model, $code = 200) {
		parent::__construct('', $code);

		$this->model = $model;
		$this->headers->set('Conent-Type', 'application/json');
	}

	public function getContent() {
		return json_encode($this->getModel());
	}

	public function getModel() {
		return $this->model;
	}
}
