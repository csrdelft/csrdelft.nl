<?php

namespace CsrDelft\common\Logging;

use Symfony\Component\HttpFoundation\Request;
use CsrDelft\service\security\LoginService;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class LogProcessor implements ProcessorInterface
{
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly Security $security
	) {
	}

	public function __invoke(LogRecord $record): LogRecord
	{
		$request = $this->requestStack->getCurrentRequest();

		if ($request instanceof Request) {
			$record->extra['uid'] =
				$this->security->getUser()?->getUserIdentifier() ??
				LoginService::UID_EXTERN;
		}

		return $record;
	}
}
