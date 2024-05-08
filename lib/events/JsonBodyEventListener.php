<?php

namespace CsrDelft\events;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class JsonBodyEventListener
{
	/**
	 * Lees een application/json request body in de request parameter bag.
	 *
	 * @param RequestEvent $event
	 */
	public function onKernelRequest(RequestEvent $event): void
	{
		$request = $event->getRequest();
		if (
			0 === strpos($request->headers->get('Content-Type') ?? '', 'application/json')
		) {
			$data = json_decode($request->getContent(), true);
			$request->request->replace(is_array($data) ? $data : []);
		}
	}
}
