<?php


namespace CsrDelft\common\Logging;


use CsrDelft\model\security\LoginModel;
use Symfony\Component\HttpFoundation\RequestStack;

class LogProcessor {
	private $requestStack;

	public function __construct(RequestStack $requestStack) {
		$this->requestStack = $requestStack;
	}

	public function processRecord(array $record) {
		$request = $this->requestStack->getCurrentRequest();

		if ($request) {
			$record['extra']['url'] = $request->getRequestUri();
			$record['extra']['uid'] = LoginModel::getUid();
		}

		return $record;
	}
}
