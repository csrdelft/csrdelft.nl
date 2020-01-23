<?php

namespace CsrDelft\events;

use CsrDelft\common\CsrException;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ViewEventListener {
	/**
	 * Maak het mogelijk om een View klasse te returnen van een controller.
	 * Deze wordt dan in een Response gewrapped.
	 * @param ViewEvent $event
	 */
	public function onKernelView(ViewEvent $event) {
		$value = $event->getControllerResult();

		if ($value instanceof ToResponse) {
			$event->setResponse($value->toResponse());
		}
	}
}
