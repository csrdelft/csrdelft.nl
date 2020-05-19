<?php


namespace CsrDelft\common;


use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class SuDataCollector extends DataCollector {

	/**
	 * Collects data for the given Request and Response.
	 * @param Request $request
	 * @param Response $response
	 * @param Throwable $exception
	 */
	public function collect(Request $request, Response $response, Throwable $exception = null) {
		$this->data = [
			'can_su' => LoginService::mag(P_ADMIN) || ContainerFacade::getContainer()->get(SuService::class)->isSued(),
			'is_sued' => ContainerFacade::getContainer()->get(SuService::class)->isSued(),
			'profiel' => LoginService::getProfiel(),
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

	public function getProfiel() {
		return $this->data['profiel'];
	}

	public function getIsSued() {
		return $this->data['is_sued'];
	}

	public function getCanSu() {
		return $this->data['can_su'];
	}
}
