<?php

namespace CsrDelft\view;

use Symfony\Component\HttpFoundation\Response;

/**
 * JsonResponse.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
abstract class JsonResponse extends Response
{
	public function __construct(protected $model, $code = 200)
	{
		parent::__construct('', $code);

		$this->setContent(json_encode($this->getModel()));
		$this->headers->set('Content-Type', 'application/json');
	}

	public function getModel()
	{
		return $this->model;
	}
}
