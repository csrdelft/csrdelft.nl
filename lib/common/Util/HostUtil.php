<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;

final class HostUtil
{
	/**
	 * Is de huidige host de production server?
	 * @return boolean
	 */
	public static function isProduction()
	{
		// Controleer voor alle namen die de production server had/heeft/zal hebben
		return (php_uname('n') === 'syrinx'
			|| php_uname('n') === 'tzdturbo'
			|| php_uname('n') === 'albatros'
		);
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
