<?php

namespace CsrDelft\events;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class LogEventListener
{
	public function __construct(private readonly LoggerInterface $logger)
	{
	}

	/**
	 * Schrijft een log naar de 'access' logger
	 *
	 * @param RequestEvent $event
	 */
	public function onKernelRequest(RequestEvent $event)
	{
		$request = $event->getRequest();

		$this->logger->info($request->getRequestUri(), [
			'ip' => $request->getClientIp(),
			'user-agent' => $request->headers->get('User-Agent'),
			'referer' => $request->headers->get('Referer'),
		]);
	}
}
