<?php

namespace CsrDelft\common\Logging;

use CsrDelft\service\security\LoginService;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class LogProcessor implements ProcessorInterface
{
	/**
	 * @var RequestStack
	 */
	private $requestStack;
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(RequestStack $requestStack, Security $security)
	{
		$this->requestStack = $requestStack;
		$this->security = $security;
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
