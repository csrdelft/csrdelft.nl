<?php


namespace CsrDelft\controller;


use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ErrorController {
	public function handleException(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null) {
		switch ($exception->getClass()) {
			case CsrGebruikerException::class:
				return view('fout.400', ['bericth' => $exception->getMessage()]);
			case NotFoundHttpException::class:
				return view('fout.404');
			case AccessDeniedException::class:
			case CsrToegangException::class:
				return view('fout.403');
		}
		return view('fout.500');
	}

}
