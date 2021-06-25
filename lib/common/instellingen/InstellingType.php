<?php

namespace CsrDelft\common\instellingen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 15/07/2019
 */
class InstellingType {
	const Enumeration = 'Enumeration';
	const Integer = 'Integer';
	const String = 'String';

	public static function getTypeOptions() {
		return [
			self::Enumeration => self::Enumeration,
			self::Integer => self::Integer,
			self::String => self::String,
		];
	}
}
