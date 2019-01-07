<?php

namespace CsrDelft\model;

/**
 * Om aan te geven of een klasse foreign keys heeft.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
trait ForeignKeysTrait {
	protected static $foreign_keys = [];

	public static function getForeignKeys() {
		return static::$foreign_keys;
	}

	public static function getForeignKey($foreignClass) {
		return array_search($foreignClass, static::$foreign_keys);
	}
}
