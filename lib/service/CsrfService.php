<?php


namespace CsrDelft\service;


use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfService {
	private static $instance;
	static function instance() : CsrfService {
		if (static::$instance === null)
			static::$instance = static::init();
		return static::$instance;
	}
	/**
	 * CsrfService constructor.
	 * @param $manager CsrfTokenManagerInterface
	 */
	public function __construct($manager) {
		$this->manager = $manager;
	}
	/**
	 * @var CsrfTokenManagerInterface
	 */
	private $manager;

	private static function init() : CsrfService {
		return new CsrfService(new CsrfTokenManager());
	}

	/**
	 * @param $token
	 * @param string $path
	 * @param string $method
	 * @return bool
	 */
	public function isValid ($token, string $path, string $method) : bool {
		if (session_status() == PHP_SESSION_NONE || $token == null) {
			return false;
		}
		return $this->manager->isTokenValid($token);
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @return CsrfToken|null
	 */
	public function generateToken (string $path, string $method) {
		if (session_status() == PHP_SESSION_NONE) {
			return null;
		}
		return $this->manager->getToken("global");
	}

	public static function preventCsrf() {
		$method = filter_input(INPUT_SERVER,'REQUEST_METHOD', FILTER_SANITIZE_STRING);
		if (strtolower($method) == 'get') {
			return null;
		}
		$id = filter_input(INPUT_SERVER,'HTTP_X_CSRF_ID', FILTER_SANITIZE_STRING);
		$value = filter_input(INPUT_SERVER,'HTTP_X_CSRF_VALUE', FILTER_SANITIZE_STRING);
		if ($id == null || $value == null) {
			$id = filter_input(INPUT_POST,'X-CSRF-ID', FILTER_SANITIZE_STRING);
			$value = filter_input(INPUT_POST,'X-CSRF-VALUE', FILTER_SANITIZE_STRING);
		}
		if ($id != null && $value != null) {
			$token = new CsrfToken($id, $value);
			$url = filter_input(INPUT_SERVER,'REQUEST_URI', FILTER_SANITIZE_STRING);
			if (CsrfService::instance()->isValid($token, $url, $method)) {
				return null;
			}
		}
		// No valid token has been posted, so we redirect to prevent sensitive operations from taking place
		redirect();
	}
}
