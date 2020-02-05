<?php

use CsrDelft\common\Ini;

$inis = [
	'slack' => Ini::SLACK,
];

// Lees per INI bestand en sla op als parameters
foreach ($inis as $name => $location) {
	$config = Ini::lees($location);
	foreach($config as $key => $value) {
		$container->setParameter("app.{$name}.{$key}", $value);
	}
}
