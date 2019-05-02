<?php

namespace CsrDelft\model\entity\groepen;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeType extends PersistentEnum {
	const CHECKBOX = 'checkbox_1';
	const RADIOS = 'radios_1';
	const TEXT = 'text_1';

	protected static $supportedChoices = [
		self::CHECKBOX => self::CHECKBOX,
		self::RADIOS => self::RADIOS,
		self::TEXT => self::TEXT,
	];

	protected static $mapChoiceToDescription = [
		self::CHECKBOX => 'Een checkbox',
		self::RADIOS => 'Radiobuttons',
		self::TEXT => 'Een textbox',
	];
}
