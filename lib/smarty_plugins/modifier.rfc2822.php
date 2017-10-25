<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     rfc2822<br>
 * Date:     August, 29 2007
 * Purpose:  return a date formatted according to RFC 2822
 * Input:    date or timestamp to be formatted
 * Example:  {$smarty.now|rfc2822}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_rfc2822($date) {
	if (strlen($date) == strlen((int)$date)) {
		return date('r', $date);
	} else {
		return date('r', strtotime($date));
	}
}
