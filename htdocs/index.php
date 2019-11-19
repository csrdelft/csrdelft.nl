<?php

use CsrDelft\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/lib/configuratie.include.php';

/*
 * Als je Symfony op een plek wil gebruiken waar dat nog niet kan.
 */
global $kernel;

if ($_SERVER['APP_DEBUG']) {
	umask(0000);

	Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
	Request::setTrustedProxies(
		explode(',', $trustedProxies),
		Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST
	);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
	Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
