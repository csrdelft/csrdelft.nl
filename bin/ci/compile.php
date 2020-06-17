<?php

use CsrDelft\view\renderer\BladeRenderer;

/**
 * @throws Exception
 */
function compileBlade() {
	$bladeExtension = ".blade.php";

	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TEMPLATE_PATH, FilesystemIterator::UNIX_PATHS));

	echo "Compiling blade templates in " . TEMPLATE_PATH . PHP_EOL;

	echo "BladeOne mode: " . BLADEONE_MODE . PHP_EOL;

	foreach ($files as $file) {
		if (endsWith($file, $bladeExtension)) {
			$file = str_replace(TEMPLATE_PATH, '', $file);
			$file = str_replace($bladeExtension, '', $file);
			$file = str_replace('/', '.', $file);
			echo "Compiling template " . $file . PHP_EOL;
			$renderer = new BladeRenderer($file);
			$renderer->compile();
		}
	}
}

try {
	require_once __DIR__ . '/../../lib/configuratie.include.php';

	compileBlade();
} catch (Exception $ex) {
	echo $ex->getMessage();
	exit(-1);
}
