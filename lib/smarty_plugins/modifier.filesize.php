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
 * Name:     filesize<br>
 * Date:     October 11, 2010
 * Purpose:  process file size in bytes to better human readable KB or MB size.
 * Input:    file size in bytes
 * Example:  {10234|filesize}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */

if (!function_exists('format_filesize')) {
	function format_filesize($size) {
		$units = array(' B', ' KB', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2) . $units[$i];
	}
}
function smarty_modifier_filesize($size) {
	$size = (int)$size;
	return format_filesize((int)$size);

}
