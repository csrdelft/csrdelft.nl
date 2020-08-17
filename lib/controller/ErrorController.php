<?php


namespace CsrDelft\controller;


use CsrDelft\common\ShutdownHandler;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Throwable;


class ErrorController extends AbstractController {
	use TargetPathTrait;

	public function handleException(RequestStack $requestStack, Throwable $exception) {
		$request = $requestStack->getMasterRequest();

		$statusCode = 500;
		if (method_exists($exception, 'getStatusCode')) {
			$statusCode = $exception->getStatusCode();
		}

		if (!in_array($statusCode, [
				Response::HTTP_BAD_REQUEST,
				Response::HTTP_NOT_FOUND,
				Response::HTTP_FORBIDDEN,
				Response::HTTP_METHOD_NOT_ALLOWED,
			]) &&
			!in_array(get_class($exception), [
				AccessDeniedException::class
			])) {
			ShutdownHandler::emailException($exception);
			ShutdownHandler::slackException($exception);
			ShutdownHandler::touchHandler();
		}

		if ($request->getMethod() == 'POST') {
			return new Response($exception->getMessage(), $statusCode);
		}

		switch ($statusCode) {
			case Response::HTTP_BAD_REQUEST:
			{
				return new Response(view('fout.400', ['bericht' => $exception->getMessage()]), Response::HTTP_BAD_REQUEST);
			}
			case Response::HTTP_NOT_FOUND:
			{
				return new Response(view('fout.404', ['bericht' => $exception->getMessage()]), Response::HTTP_NOT_FOUND);
			}
			case Response::HTTP_FORBIDDEN:
			{
				if (LoginService::getUid() == LoginService::UID_EXTERN) {
					$requestUri = $request->getRequestUri();
					$router = $this->get('router');

					$this->saveTargetPath($request->getSession(), 'main', $requestUri);

					return new RedirectResponse($router->generate('csrdelft_login_loginform'));
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
}
