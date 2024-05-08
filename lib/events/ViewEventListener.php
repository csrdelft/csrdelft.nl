<?php

namespace CsrDelft\events;

use CsrDelft\view\ToResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ViewEventListener
{
	/**
	 * Maak het mogelijk om een View klasse te returnen van een controller.
	 * Deze wordt dan in een Response gewrapped.
	 * @param ViewEvent $event
	 */
	public function onKernelView(ViewEvent $event): void
	{
		$value = $event->getControllerResult();

		if ($value instanceof ToResponse) {
			$event->setResponse($value->toResponse());
		}
	}
}
