<?php

use CsrDelft\model\entity\Geslacht;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\security\AccessAction;

require_once __DIR__ . '/../../lib/configuratie.include.php';

const FILE_TEMPLATE = __DIR__ . '/../../resources/assets/js/enum/%s.js';
const ENUMS = [
	AccessAction::class,
	LidStatus::class,
	Geslacht::class
];

/**
 * @throws Exception
 */
function generateEnums() {
	foreach (ENUMS as $enum) {
		generateJS($enum);
	}
}

/**
 * @param \CsrDelft\Orm\Entity\PersistentEnum $enum
 * @throws Exception
 */
function generateJS($enum) {
	$reflectionClass = new ReflectionClass($enum);

	$classConstants = $reflectionClass->getConstants();

	$typeOptions = $enum::getTypeOptions();

	$options = [];
	foreach ($classConstants as $name => $option) {
		if (in_array($option, $typeOptions)) {

			$options[$name] = [
				'value' => $option,
				'description' => $enum::getDescription($option),
				'char' => $enum::getChar($option)
			];
		}
	}

	$optionsString = json_encode($options, JSON_PRETTY_PRINT);

	/** @noinspection JSUnusedGlobalSymbols */
	$js = <<<JS
/**
* NIET AANPASSEN.
* Gegenereerde code voor {$enum}.
* 
* Zie bin/dev/generator.enum.php voor generator.
*/
export default {$optionsString};
JS;

	$fp = fopen(sprintf(FILE_TEMPLATE, $reflectionClass->getShortName()), 'w');

	fwrite($fp, $js);
	fclose($fp);
}
