<?php

use CsrDelft\entity\Geslacht;
use CsrDelft\entity\groepen\enum\GroepKeuzeType;
use CsrDelft\entity\security\enum\AccessAction;
use CsrDelft\model\entity\LidStatus;

require_once __DIR__ . '/../../lib/configuratie.include.php';

const FILE_TEMPLATE = __DIR__ . '/../../assets/js/enum/%s.ts';
const ENUMS = [
	AccessAction::class,
	LidStatus::class,
	Geslacht::class,
	GroepKeuzeType::class,
];

/**
 * @throws Exception
 */
function generateEnums()
{
	foreach (ENUMS as $enum) {
		generateTypescript($enum);
	}
}

/**
 * @param string|\CsrDelft\common\Enum $enum
 * @throws ReflectionException
 */
function generateTypescript($enum)
{
	$reflectionClass = new ReflectionClass($enum);
	$className = $reflectionClass->getShortName();

	$classConstants = $reflectionClass->getConstants();

	$typeOptions = $enum::getEnumValues();

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
			$description = $enum::from($option)->getDescription();
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

export function get{$className}Description(option: string): string {
	return {
{$descriptionString}	}[option];
}

TS;

	$fp = fopen(sprintf(FILE_TEMPLATE, $reflectionClass->getShortName()), 'w');

	fwrite($fp, $js);
	fclose($fp);
}
