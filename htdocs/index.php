<?php

use CsrDelft\Kernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Ga niet verder als de stek in onderhoudsmodus staat.
 */
if (file_exists(__DIR__ . '/../.onderhoud')) {
	http_response_code(503);
	echo <<<'HTML'
<!doctype html>
<html lang=nl>
<title>C.S.R. Delft - Onderhoud</title>
<meta charset=utf-8>
<meta name=viewport content="width=device-width, initial-scale=1.0">
<meta http-equiv="refresh" content="5">

<body style="font-family: sans-serif; text-align: center;">
<h1>Onderhoud</h1>
<p>De website is momenteel in onderhoud. Dit duurt meestal niet lang.</p>
<img alt="Beeldmerk van de Vereniging" src="/plaetjes/layout-extern/Logo.svg" width="200">
HTML;
	exit;
}

/** @var Kernel $kernel */
$kernel = require dirname(__DIR__) . '/lib/configuratie.include.php';

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
