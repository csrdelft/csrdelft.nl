<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ErrorController {
	public function handleException(RequestStack $requestStack, FlattenException $exception) {
		if ($requestStack->getMasterRequest()->getMethod() == 'POST') {
			return new Response($exception->getMessage(), $this->getCode($exception));
		}

		switch ($this->getCode($exception)) {
			case Response::HTTP_BAD_REQUEST: return view('fout.400', ['bericht' => $exception->getMessage()]);
			case Response::HTTP_NOT_FOUND: return view('fout.404');
			case Response::HTTP_FORBIDDEN: return view('fout.403');
			case Response::HTTP_METHOD_NOT_ALLOWED: return view('fout.405');
		}

		return view('fout.500');
	}

	private function getCode(FlattenException $exception) {
		switch ($exception->getClass()) {
			case CsrGebruikerException::class:
				return Response::HTTP_BAD_REQUEST;
			case NotFoundHttpException::class:
				return Response::HTTP_NOT_FOUND;
			case AccessDeniedException::class:
			case CsrToegangException::class:
				return Response::HTTP_FORBIDDEN;
			case MethodNotAllowedHttpException::class:
				return Response::HTTP_METHOD_NOT_ALLOWED;
		}

		return Response::HTTP_INTERNAL_SERVER_ERROR;
	}

}
