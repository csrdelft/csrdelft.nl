<?php

namespace CsrDelft\service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfService
{


	/**
	 * @param string $path
	 * @param string $method
	 */
	public function generateToken($path, string $method): CsrfToken
	{
		return $this->manager->getToken('global');
	}

	/**
	 * Controleert of de huidige request een geldige CSRF token heeft.
	 *
	 * @param Request $request
	 * @return bool
	 */
	public function preventCsrf(Request $request)
	{
		// Safe: GET, OPTIONS, HEAD, TRACE
		if ($request->isMethodSafe()) {
			return true;
		}
		$id = $request->request->get('X-CSRF-ID');
		$value = $request->request->get('X-CSRF-VALUE');

		if ($id == null || $value == null) {
			$id = $request->server->get('HTTP_X_CSRF_ID');
			$value = $request->server->get('HTTP_X_CSRF_VALUE');
		}
		$url = $request->getRequestUri();
		if ($id != null && $value != null) {
			$token = new CsrfToken($id, $value);
			if ($this->isValid($token, $url, $request->getMethod())) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $token
	 * @param string $path
	 * @param string $method
	 * @return bool
	 */
	public function isValid(CsrfToken $token, string $path, string $method): bool
	{
		return $this->manager->isTokenValid($token);
	}
}
