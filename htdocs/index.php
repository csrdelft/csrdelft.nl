<?php

use CsrDelft\Kernel;
use Symfony\Component\HttpFoundation\Request;

/** @var Kernel $kernel */
$kernel = require dirname(__DIR__) . '/lib/configuratie.include.php';

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
