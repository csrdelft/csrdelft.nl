<?php


namespace CsrDelft\common;


use CsrDelft\model\security\LoginModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class SuDataCollector extends DataCollector {

	/**
	 * Collects data for the given Request and Response.
	 * @param Request $request
	 * @param Response $response
	 * @param \Exception|null $exception
	 */
	public function collect(Request $request, Response $response, \Exception $exception = null) {
		$this->data = [
			'sued_from' => LoginModel::getSuedFrom(),
			'profiel' => LoginModel::getProfiel(),
		];
	}

	/**
	 * Returns the name of the collector.
	 *
	 * @return string The collector name
	 */
	public function getName() {
		return 'csr.su';
	}

	public function reset() {
		$this->data = [];
	}

	public function getSuedFrom() {
		return $this->data['sued_from'];
	}

	public function getProfiel() {
		return $this->data['profiel'];
	}
}
