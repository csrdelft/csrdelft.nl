<?php

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\GroepKeuzeType;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\security\AccessAction;

require_once __DIR__ . '/../../lib/configuratie.include.php';

const FILE_TEMPLATE = __DIR__ . '/../../resources/assets/js/enum/%s.ts';
const ENUMS = [
	AccessAction::class,
	LidStatus::class,
	Geslacht::class,
	GroepKeuzeType::class,
];

/**
 * @throws Exception
 */
function generateEnums() {
	foreach (ENUMS as $enum) {
		generateTypescript($enum);
	}
}

/**
 * @param \CsrDelft\Orm\Entity\PersistentEnum $enum
 * @throws Exception
 */
function generateTypescript($enum) {
	$reflectionClass = new ReflectionClass($enum);
	$className = $reflectionClass->getShortName();

	$classConstants = $reflectionClass->getConstants();

	$typeOptions = $enum::getTypeOptions();

	ob_start();
	foreach ($classConstants as $name => $option) {
		if (in_array($option, $typeOptions)) {
			echo "	$name: '$option',\n";
		}
	}

	$optionsString = ob_get_clean();

	ob_start();

	foreach ($classConstants as $name => $option) {
		if (in_array($option, $typeOptions)) {
			$description = $enum::getDescription($option);
			echo "		$option: '$description',\n";
		}
	}

	$descriptionString = ob_get_clean();

	$js = <<<TS
/**
 * NIET AANPASSEN.
 * Gegenereerde code voor {$enum}.
 *
 * Zie bin/dev/generator.enum.php voor generator.
 */
export default {
{$optionsString}};

export function get{$className}Description(option: string) {
	return {
{$descriptionString}	}[option];
}

TS;

	$fp = fopen(sprintf(FILE_TEMPLATE, $reflectionClass->getShortName()), 'w');

	fwrite($fp, $js);
	fclose($fp);
}
