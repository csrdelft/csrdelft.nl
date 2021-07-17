<?php

namespace CsrDelft\entity\groepen\enum;

use CsrDelft\common\Enum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 *
 * @method static static V1
 * @method static static V2
 * @method static boolean isV1(static $groepVersie)
 * @method static boolean isV2(static $groepVersie)
 */
class GroepVersie extends Enum {
	const V1 = 'v1';
	const V2 = 'v2';

	protected static $mapChoiceToDescription = [
		self::V1 => 'Versie 1',
		self::V2 => 'Versie 2',
	];
}
