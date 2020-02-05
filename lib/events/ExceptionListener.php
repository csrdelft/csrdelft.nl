<?php

namespace CsrDelft\events;

use CsrDelft\common\ShutdownHandler;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener {
	/**
	 * Trigger showdown handler op kernel exceptions (geconfigureerd in services.yaml)
	 * @param ExceptionEvent $event
	 */
	public function onKernelException(ExceptionEvent $event) {
		if (!DEBUG) {
			$exception = $event->getThrowable();
			if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() === 500) {
				ShutdownHandler::slackShutdownHandler();
			}
		}
	}
}
