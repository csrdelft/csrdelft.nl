<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 *
 * @method static static CHECKBOX
 * @method static static DROPDOWN
 * @method static static RADIOS
 * @method static static TEXT
 */
class GroepKeuzeType extends Enum {
	const CHECKBOX = 'checkbox_1';
	const DROPDOWN = 'dropdown_1';
	const RADIOS = 'radios_1';
	const TEXT = 'text_1';

	protected static $mapChoiceToDescription = [
		self::CHECKBOX => 'Een checkbox',
		self::DROPDOWN => 'Een dropdown',
		self::RADIOS => 'Radiobuttons',
		self::TEXT => 'Een textbox',
	];
}
