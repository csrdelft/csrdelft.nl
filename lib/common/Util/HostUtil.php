<?php

namespace CsrDelft\common\Util;

final class HostUtil
{
	/**
	 * Is de huidige host genaamd 'syrinx'?
	 * @return boolean
	 */
	public static function isSyrinx()
	{
		return 'syrinx' === php_uname('n');
	}

	public static function isCLI()
	{
		return PHP_SAPI == 'cli' && $_SERVER['APP_ENV'] != 'test';
	}

	public static function isCI()
	{
		return getenv('CI');
	}
}
