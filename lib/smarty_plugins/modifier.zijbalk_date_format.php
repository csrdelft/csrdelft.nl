<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Formatteer een datum voor de zijbalk.
 *
 *  - Als dezelfe dag:     13:13
 *  - Als dezelfde maand:  ma 06
 *  - Anders:              06-12
 *
 * @version 1.0
 * @param string|integer
 * @return string
 */
function smarty_modifier_zijbalk_date_format($datetime) {
	if (!is_int($datetime)) {
		$datetime = strtotime($datetime);
	}

	if (date('d-m', $datetime) === date('d-m')) {
		return strftime('%H:%M', $datetime);
	} elseif (strftime('%U', $datetime) === strftime('%U')) {
		return strftime('%a&nbsp;%d', $datetime);
	} else {
		return strftime('%d-%m', $datetime);
	}
}
