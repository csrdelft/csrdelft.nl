<?php

namespace CsrDelft\common\Logging;

use CsrDelft\service\security\LoginService;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class LogProcessor implements ProcessorInterface
{
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly Security $security
	) {
	}

	public function __invoke(LogRecord $record)
	{
		$request = $this->requestStack->getCurrentRequest();

		if ($request) {
			$record->extra['uid'] = $this->security->getUser()
				? $this->security->getUser()->getUserIdentifier()
				: LoginService::UID_EXTERN;
		}

		return $record;
	}
}
