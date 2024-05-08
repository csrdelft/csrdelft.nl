<?php

namespace CsrDelft\controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Throwable;

class ErrorController extends AbstractController
{
	use TargetPathTrait;

	public function handleException(RequestStack $requestStack, Throwable $exception): Response|RedirectResponse {
		$request = $requestStack->getMainRequest();

		$statusCode = 500;
		if (method_exists($exception, 'getStatusCode')) {
			$statusCode = $exception->getStatusCode();
		}

		if ($request->getMethod() == 'POST') {
			return new Response($exception->getMessage(), $statusCode);
		}

		switch ($statusCode) {
			case Response::HTTP_BAD_REQUEST:
				$response = new Response(null, Response::HTTP_BAD_REQUEST);
				return $this->render(
					'fout/400.html.twig',
					['bericht' => $exception->getMessage()],
					$response
				);
			case Response::HTTP_NOT_FOUND:
				$response = new Response(null, Response::HTTP_NOT_FOUND);
				return $this->render(
					'fout/404.html.twig',
					['bericht' => $exception->getMessage()],
					$response
				);
			case Response::HTTP_FORBIDDEN:
				if ($this->getUser() == null) {
					$requestUri = $request->getRequestUri();
					$router = $this->get('router');

					$this->saveTargetPath($request->getSession(), 'main', $requestUri);

					return new RedirectResponse(
						$router->generate('csrdelft_login_loginform')
					);
				}

				$response = new Response(null, Response::HTTP_FORBIDDEN);
				return $this->render('fout/403.html.twig', [], $response);
			case Response::HTTP_METHOD_NOT_ALLOWED:
				$response = new Response(null, Response::HTTP_METHOD_NOT_ALLOWED);
				return $this->render('fout/405.html.twig', [], $response);
			default:
				$response = new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
				return $this->render('fout/500.html.twig', [], $response);
		}
	}
}
