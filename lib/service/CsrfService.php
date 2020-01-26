<?php


namespace CsrDelft\service;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfService {
	/**
	 * @var CsrfTokenManagerInterface
	 */
	private $manager;

	/**
	 * CsrfService constructor.
	 * @param $manager CsrfTokenManagerInterface
	 */
	public function __construct(CsrfTokenManagerInterface $manager) {
		$this->manager = $manager;
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @return CsrfToken|null
	 */
	public function generateToken(string $path, string $method) {
		if (session_status() == PHP_SESSION_NONE) {
			return null;
		}
		return $this->manager->getToken("global");
	}

	public function preventCsrf() {
		$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING);
		if (strtolower($method) == 'get') {
			return null;
		}
		$id = filter_input(INPUT_SERVER, 'HTTP_X_CSRF_ID', FILTER_SANITIZE_STRING);
		$value = filter_input(INPUT_SERVER, 'HTTP_X_CSRF_VALUE', FILTER_SANITIZE_STRING);
		if ($id == null || $value == null) {
			$id = filter_input(INPUT_POST, 'X-CSRF-ID', FILTER_SANITIZE_STRING);
			$value = filter_input(INPUT_POST, 'X-CSRF-VALUE', FILTER_SANITIZE_STRING);
		}
		if ($id != null && $value != null) {
			$token = new CsrfToken($id, $value);
			$url = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRING);
			if ($this->isValid($token, $url, $method)) {
				return null;
			}
		}
		// No valid token has been posted, so we redirect to prevent sensitive operations from taking place
		redirect();
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
