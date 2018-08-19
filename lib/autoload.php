<?php

spl_autoload_register(function ($class) {
	// project-specific namespace prefix
	$prefix = 'CsrDelft\\';

	// base directory for the namespace prefix
	$base_dir = __DIR__ . DIRECTORY_SEPARATOR;

	// does the class use the namespace prefix?
	$len = strlen($prefix);
	if (strncmp($prefix, $class, $len) !== 0) {
		// no, move to the next registered autoloader
		return;
	}

	// get the relative class name
	$relative_class = substr($class, $len);

	// replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace('\\', '/', $relative_class);

	$extensions = [
		'.class.php',
		'.interface.php',
		'.php',
		'.abstract.php',
		'.static.php',
		'.enum.php'
	];

	foreach ($extensions as $extension) {
		$fileFull = $file . $extension;
		if (file_exists($fileFull)) {
			require $fileFull;
			return; // Done
		}
	}
});