<?php

namespace CsrDelft\events;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiTrustedHostsEventListener
{
	public function onKernelRequest(RequestEvent $event)
	{
		$request = $event->getRequest();

		if (!str_starts_with($request->getUri(), '/API/2.0')) {
			return;
		}

		if (
			$request->server->has('HTTP_ORIGIN') &&
			in_array(
				$request->server->get('HTTP_ORIGIN'),
				explode(',', API_ORIGINS),
				true
			)
		) {
			$headers = $event->getResponse()->headers;
			$headers->set(
				'Access-Control-Allow-Origin: ',
				$request->server->get('HTTP_ORIGIN')
			);
			$headers->set('AccessControl-Max-Age', '1440');
			$headers->set(
				'Access-Control-Allow-Headers',
				'Accept, Origin, Content-Type, X-Csr-Authorization'
			);
			$headers->set(
				'Access-Control-Allow-Methods',
				'PUT, GET, POST, DELETE, OPTIONS'
			);

			if ($request->isMethod('OPTIONS')) {
				$event->setResponse(new Response('', 204));
			}
		}
	}
}
