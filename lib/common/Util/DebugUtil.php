<?php

namespace CsrDelft\common\Util;

use CsrDelft\common\ContainerFacade;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;

final class DebugUtil
{
	/**
	 * print_r een variabele met <pre>-tags eromheen.
	 *
	 * @param string $cssID
	 * @param false|null|string $sString
	 */
	public static function debugprint(string|false|null $sString, $cssID = 'pubcie_debug'): void
	{
		if (
			DEBUG ||
			LoginService::mag(P_ADMIN) ||
			ContainerFacade::getContainer()
				->get(SuService::class)
				->isSued()
		) {
			echo '<pre class="' . $cssID . '">' . print_r($sString, true) . '</pre>';
		}
	}
}
