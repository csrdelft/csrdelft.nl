<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {icon} function plugin
 *
 * Type:     function
 * Name:     icon
 * Date:     Nov 4, 2009
 * Purpose:  Icon-generator
 * Input:
 *
 * Examples:
 * <pre>
 * 	{icon get='verwijderen'}
 * 	{icon get='verwijderen' notag}
 * </pre>
 * @link http://feuten.csrdelft.nl
 * @version  1.1
 * @author   Jan Pieter Waagmeester <jpwaag@jpwaag.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */
require_once 'icon.class.php';

function smarty_function_icon($params, &$smarty) {

	if (array_key_exists('get', $params)) {

		$title = null;
		if (array_key_exists('title', $params)) {
			$title = $params['title'];
		}

		if (array_key_exists('notag', $params)) {
			return Icon::get($params['get'], $title);
		} else {
			return Icon::getTag($params['get'], $title);
		}
	}
}
