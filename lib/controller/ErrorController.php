<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\security\LoginModel;
use Psr\Container\ContainerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ErrorController {
	public function handleException(RequestStack $requestStack, FlattenException $exception, ContainerInterface $container) {
		$request = $requestStack->getMasterRequest();

		if ($request->getMethod() == 'POST') {
			return new Response($exception->getMessage(), $this->getCode($exception));
		}

		switch ($this->getCode($exception->getClass())) {
			case Response::HTTP_BAD_REQUEST:
			{
				return new Response(view('fout.400', ['bericht' => $exception->getMessage()]), Response::HTTP_BAD_REQUEST);
			}
			case Response::HTTP_NOT_FOUND:
			{
				return new Response(view('fout.404'), Response::HTTP_NOT_FOUND);
			}
			case Response::HTTP_FORBIDDEN:
			{
				if (LoginModel::getUid() == 'x999') {
					$requestUri = $request->getRequestUri();
					$router = $container->get('router');

					return new RedirectResponse($router->generate('login-form', ['redirect' => urlencode($requestUri)]));
				}

				return new Response(view('fout.403'), Response::HTTP_FORBIDDEN);
			}
			case Response::HTTP_METHOD_NOT_ALLOWED:
			{
				return new Response(view('fout.405'), Response::HTTP_METHOD_NOT_ALLOWED);
			}
			default:
			{
				return new Response(view('fout.500'), Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
	}

	/**
	 * Map een Exception class naar een error code.
	 * @param string $exception
	 * @return int
	 */
	private function getCode($exception) {
		switch ($exception) {
			case CsrGebruikerException::class:
				return Response::HTTP_BAD_REQUEST;
			case NotFoundHttpException::class:
				return Response::HTTP_NOT_FOUND;
			case AccessDeniedException::class:
			case CsrToegangException::class:
				return Response::HTTP_FORBIDDEN;
			case MethodNotAllowedHttpException::class:
				return Response::HTTP_METHOD_NOT_ALLOWED;
			default:
				return Response::HTTP_INTERNAL_SERVER_ERROR;
		}
	}
}
