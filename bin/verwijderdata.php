<?php
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\profiel\LidStatusService;

chdir(dirname(__FILE__) . '/../lib/');

/** @var \CsrDelft\Kernel $kernel */
$kernel = require_once 'configuratie.include.php';

$container = $kernel->getContainer();
$profielRepository = $container->get(ProfielRepository::class);
$lidStatusService = $container->get(LidStatusService::class);

foreach ($profielRepository->findAll() as $profiel) {
	if ($lidStatusService->verwijderVeldenUpdate($profiel)) {
		echo 'Verwijder data van ' . $profiel->uid . "\n";
	}
}
