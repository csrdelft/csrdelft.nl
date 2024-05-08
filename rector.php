<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;

return function (RectorConfig $rectorConfig): void {
	$rectorConfig->importNames(importNames: true, importDocBlockNames: true);
	$rectorConfig->importShortClasses(true);
	$rectorConfig->paths([
		__DIR__ . '/tests',
		__DIR__ . '/lib'
	]);
	$rectorConfig->sets([
		DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
		SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
		SensiolabsSetList::ANNOTATIONS_TO_ATTRIBUTES
	]);

	$rectorConfig->rules([
		AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
		ReturnTypeFromStrictParamRector::class,
		ReturnTypeFromStrictTernaryRector::class,
		ReturnTypeFromStrictNewArrayRector::class,
		ReturnTypeFromStrictScalarReturnExprRector::class,
		ReturnTypeFromStrictTypedCallRector::class,
		ReturnTypeFromStrictNativeCallRector::class,
		ReturnTypeFromStrictFluentReturnRector::class,
		ReturnTypeFromReturnNewRector::class,
		AddClosureVoidReturnTypeWhereNoReturnRector::class,
	]);
};
