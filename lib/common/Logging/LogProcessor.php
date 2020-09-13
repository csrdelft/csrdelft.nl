<?php


namespace CsrDelft\common\Logging;


use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class LogProcessor {
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(RequestStack $requestStack, Security $security) {
		$this->requestStack = $requestStack;
		$this->security = $security;
	}

	public function processRecord(array $record) {
		$request = $this->requestStack->getCurrentRequest();

		if ($request) {
			$record['extra']['url'] = $request->getRequestUri();
			$record['extra']['uid'] = $this->security->getUser() ? $this->security->getUser()->getUsername() : LoginService::UID_EXTERN;
		}

		return $record;
	}
}
