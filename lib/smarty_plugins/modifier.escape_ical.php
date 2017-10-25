<?php

/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

/**
 * Ical escape modifier plugin
 * Type:     modifier<br>
 * Name:     escape_ical<br>
 * Purpose:  escape string for ical output
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @param string $string
 * @param int $prefix_length
 *
 * @return string
 */
function smarty_modifier_escape_ical($string, $prefix_length) {
	$string = str_replace('\\', '\\\\', $string);
	$string = str_replace("\r", '', $string);
	$string = str_replace("\n", '\n', $string);
	$string = str_replace(';', '\;', $string);
	$string = str_replace(',', '\,', $string);

	$length = 60 - (int)$prefix_length;
	$wrap = mb_substr($string, 0, $length);
	$rest = mb_substr($string, $length);
	if (!empty($rest)) {
		$wrap .= "\r\n " . wordwrap($rest, 59, "\r\n ", true);
	}
	return $wrap;
}
