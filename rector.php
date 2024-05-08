<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

return function (RectorConfig $rectorConfig): void {
	$rectorConfig->paths([
		__DIR__ . '/tests',
		__DIR__ . '/lib'
	]);
	$rectorConfig->sets([
		DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
	]);
};
