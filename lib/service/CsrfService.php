<?php


namespace CsrDelft\service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfService {
	/**
	 * @var CsrfTokenManagerInterface
	 */
	private $manager;
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * CsrfService constructor.
	 * @param $manager CsrfTokenManagerInterface
	 * @param LoggerInterface $logger
	 */
	public function __construct(CsrfTokenManagerInterface $manager, LoggerInterface $logger) {
		$this->manager = $manager;
		$this->logger = $logger;
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @return CsrfToken|null
	 */
	public function generateToken($path, string $method) {
		return $this->manager->getToken("global");
	}

	/**
	 * Controleert of de huidige request een geldige CSRF token heeft.
	 *
	 * @param Request $request
	 * @return bool
	 */
	public function preventCsrf(Request $request) {
		// Safe: GET, OPTIONS, HEAD, TRACE
		if ($request->isMethodSafe()) {
			return true;
		}
		$id = $request->server->get('HTTP_X_CSRF_ID');
		$value = $request->server->get('HTTP_X_CSRF_VALUE');

		if ($id == null || $value == null) {
			$id = $request->request->get('X-CSRF-ID');
			$value = $request->request->get('X-CSRF-VALUE');
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
	public function isValid($token, string $path, string $method): bool {
		if (session_status() == PHP_SESSION_NONE || $token == null) {
			return false;
		}
		return $this->manager->isTokenValid($token);
	}
}
