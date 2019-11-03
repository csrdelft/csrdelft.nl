<?php

namespace CsrDelft\events;

use CsrDelft\common\CsrException;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class ViewEventListener {
	/**
	 * Maak het mogelijk om een @see View klasse te returnen van een controller.
	 * Deze wordt dan in een Response gewrapped.
	 *
	 * @param GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event) {
		$value = $event->getControllerResult();

		if ($value instanceof ToResponse) {
			$value = $value->toResponse();
		} else if (!($value instanceof Response)) {
			throw new CsrException("Value not convertible to Response");
		}
		$event->setResponse($value);
	}
}
