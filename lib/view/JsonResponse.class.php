<?php

namespace CsrDelft\view;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;


/**
 * JsonResponse.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class JsonResponse implements View, Responsable {

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

	public function __toString() {
        ob_start();
        $this->view();
        return ob_get_clean();
	}

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $response = new Response();
        $response->setStatusCode($this->code);
        $response->setContent($this);
        $response->header('Content-Type', 'application/json');

        return $response;
    }
}
