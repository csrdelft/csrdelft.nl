<?php

namespace CsrDelft\entity\groepen;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeType extends Enum {
	const CHECKBOX = 'checkbox_1';
	const DROPDOWN = 'dropdown_1';
	const RADIOS = 'radios_1';
	const TEXT = 'text_1';

	public static function CHECKBOX() {
		return static::from(self::CHECKBOX);
	}

	public static function DROPDOWN() {
		return static::from(self::DROPDOWN);
	}

	public static function RADIOS() {
		return static::from(self::RADIOS);
	}

	public static function TEXT() {
		return static::from(self::TEXT);
	}

	protected static $mapChoiceToDescription = [
		self::CHECKBOX => 'Een checkbox',
		self::DROPDOWN => 'Een dropdown',
		self::RADIOS => 'Radiobuttons',
		self::TEXT => 'Een textbox',
	];
}
