<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
	->withSymfonyContainerXml(
		__DIR__ . '/var/cache/dev/App_kernelDevDebugContainer.xml'
	)
	->withPaths([
		__DIR__ . '/config',
		__DIR__ . '/db',
		__DIR__ . '/htdocs',
		__DIR__ . '/lib',
		__DIR__ . '/tests',
	])
	// uncomment to reach your current PHP version
	->withPhpSets()
	->withSets([SymfonySetList::SYMFONY_54])
	->withTypeCoverageLevel(0);
