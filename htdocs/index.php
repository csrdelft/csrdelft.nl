<?php

use CsrDelft\Kernel;
use Symfony\Component\ErrorHandler\Debug;

/**
 * Ga niet verder als de stek in onderhoudsmodus staat.
 */
if (file_exists(__DIR__ . '/../.onderhoud')) {
	http_response_code(503);
	echo <<<'HTML'
<!DOCTYPE HTML>
<html lang="nl">
<head>
	<title>C.S.R. Delft - Onderhoud</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="refresh" content="5">
</head>
<body style="font-family: sans-serif; text-align: center;">
	<h1>Onderhoud</h1>
	<p>De website is momenteel in onderhoud. Dit duurt meestal niet lang.</p>
		<img alt="Beeldmerk van de Vereniging" src="/images/c.s.r.logo.svg" width="200">
</body>
HTML;
	exit;
}

require_once dirname(__DIR__).'/lib/defines.include.php';
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
	if ($context['APP_DEBUG']) {
		Debug::enable();
	}
	return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
