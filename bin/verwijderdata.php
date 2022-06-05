<?php
use CsrDelft\repository\ProfielRepository;

chdir(dirname(__FILE__) . '/../lib/');

/** @var \CsrDelft\Kernel $kernel */
$kernel = require_once 'configuratie.include.php';

$container = $kernel->getContainer();
$profielRepository = $container->get(ProfielRepository::class);

foreach ($profielRepository->findAll() as $profiel) {
	if ($profielRepository->verwijderVeldenUpdate($profiel)) {
		echo 'Verwijder data van ' . $profiel->uid . "\n";
	}
}
