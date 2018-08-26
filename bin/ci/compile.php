<?php

use CsrDelft\view\renderer\BladeRenderer;

// defines.include.php moet bestaan anders wil er niets werken.
if (getenv('CI')) {
	copy(__DIR__ . '/../../lib/defines.include.php.sample', __DIR__ . '/../../lib/defines.include.php');
}

/**
 * @throws Exception
 */
function compileBlade(): void {
	$bladeExtension = ".blade.php";

	$files = glob(TEMPLATE_PATH . '**/*' . $bladeExtension);

	echo "Compiling blade templates in " . TEMPLATE_PATH . PHP_EOL;

	foreach ($files as $file) {
		$file = str_replace(TEMPLATE_PATH, '', $file);
		$file = str_replace($bladeExtension, '', $file);
		echo "Compiling template " . $file . PHP_EOL;
		$renderer = new BladeRenderer($file);
		$renderer->compile();
	}
}

try {
	require_once __DIR__ . '/../../lib/configuratie.include.php';

	compileBlade();
} catch (Exception $ex) {
	echo $ex->getMessage();
	exit(-1);
}
