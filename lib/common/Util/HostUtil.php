<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;

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

	/**
	 * @return string
	 * @deprecated Gebruik relatieve url of request_stack
	 */
	public static function getCsrRoot()
	{
		$request = ContainerFacade::getContainer()
			->get('request_stack')
			->getCurrentRequest();

		return $request->getSchemeAndHttpHost();
	}
}
