<?php


namespace CsrDelft\common;


use CsrDelft\model\security\AccessModel;
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
			'can_su' => LoginModel::mag(P_ADMIN) || LoginModel::instance()->isSued(),
			'is_sued' => LoginModel::instance()->isSued(),
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

	public function getVisible() {
		return $this->data['visible'];
	}

	public function getCanSu() {
		return $this->data['can_su'];
	}
}
