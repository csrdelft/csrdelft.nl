<?php

namespace CsrDelft\model\entity;

use CsrDelft\Orm\Entity\PersistentEnum;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class BtwTarieven extends PersistentEnum {
	const BTW_ALGEMEEN_21 = 'BTW_ALGEMEEN_21';
	const BTW_VERLAAGD_9 = 'BTW_VERLAAGD_9';
	const BTW_GEEN_0 = 'BTW_GEEN_0';

	protected static $supportedChoices = [
		self::BTW_GEEN_0 => self::BTW_GEEN_0,
		self::BTW_VERLAAGD_9 => self::BTW_VERLAAGD_9,
		self::BTW_ALGEMEEN_21 => self::BTW_ALGEMEEN_21,
	];

	protected static $mapChoiceToDescription = [
		self::BTW_GEEN_0 => '0%',
		self::BTW_VERLAAGD_9 => '9%',
		self::BTW_ALGEMEEN_21 => '21%',
	];

	protected static $mapChoiceToPercentage = [
		self::BTW_GEEN_0 => 0,
		self::BTW_VERLAAGD_9 => 9,
		self::BTW_ALGEMEEN_21 => 21,
	];

	public static function getPercentage($optie) {
		return static::$mapChoiceToPercentage[$optie];
	}
}
