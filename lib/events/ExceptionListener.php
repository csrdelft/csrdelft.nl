<?php

namespace CsrDelft\events;

use CsrDelft\common\ShutdownHandler;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener {
	/**
	 * Trigger showdown handler op kernel exceptions (geconfigureerd in services.yaml)
	 * @param ExceptionEvent $event
	 */
	public function onKernelException(ExceptionEvent $event) {
		if (!DEBUG) {
			ShutdownHandler::slackShutdownHandler();
		}
	}
}
