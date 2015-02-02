<?php

/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

/**
 * Internal url modifier plugin
 * Type:     modifier<br>
 * Name:     external_url<br>
 * Purpose:  prefix url root
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @param string $url
 *
 * @return string
 */
function smarty_modifier_external_url($url) {
	if (!startsWith($url, 'http://') AND ! startsWith($url, 'https://')) {
		return CSR_ROOT . $url;
	}
	return $url;
}
