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
	 * @return CsrfToken|null
	 */
	public function isValid ($token, string $path, string $method) : bool {
		if (session_status() == PHP_SESSION_NONE || $token == null) {
			return false;
		}
		return $this->manager->isTokenValid($token);
	}
	public function generateToken (string $path, string $method) : CsrfToken{
		if (session_status() == PHP_SESSION_NONE) {
			return null;
		}
		return $this->manager->getToken("global");
	}
}