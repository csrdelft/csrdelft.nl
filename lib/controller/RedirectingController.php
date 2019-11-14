<?php


namespace CsrDelft\controller;

use Symfony\Component\HttpFoundation\Request;

class RedirectingController extends AbstractController
{
	public function removeTrailingSlashAction(Request $request)
	{
		$pathInfo = $request->getPathInfo();
		$requestUri = $request->getRequestUri();

		$url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

		// 308 (Permanent Redirect) is similar to 301 (Moved Permanently) except
		// that it does not allow changing the request method (e.g. from POST to GET)
		return $this->redirect($url, 308);
	}
}

