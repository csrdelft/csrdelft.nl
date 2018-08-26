<?php

// defines.include.php moet bestaan anders wil er niets werken.
if (getenv('CI')) {
	copy(__DIR__ . '/../../lib/defines.include.php.sample', __DIR__ . '/../../lib/defines.include.php');
}

require_once 'generator.enum.php';

try {
	generateEnums();
} catch (Exception $ex) {
	print($ex->getTraceAsString());
}
