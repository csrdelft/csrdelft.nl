<?php

use CsrDelft\common\Ini;

$inis = [
	'slack' => [
		'location' => Ini::SLACK,
		'defaults' => ['url' => 'vul-in', 'username' => '', 'channel' => '', 'icon' => '']
	],
];

foreach ($inis as $name => $options) {
	foreach ($options['defaults'] as $key => $value) {
		$container->setParameter("app.{$name}.{$key}", Ini::leesOfStandaard($options['location'], $key, $value));
	}
}
