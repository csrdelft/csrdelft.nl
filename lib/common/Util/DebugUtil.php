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
	 */
	public static function debugprint(mixed $sString, $cssID = 'pubcie_debug')
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
