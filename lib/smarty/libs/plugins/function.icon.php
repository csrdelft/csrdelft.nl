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
 *	{icon get='verwijderen'}
 *	{icon get='verwijderen' notag}
 * </pre>
 * @link http://feuten.csrdelft.nl
 * @version  1.0
 * @author   Jan Pieter Waagmeester <jpwaag@jpwaag.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */
require_once('class.icon.php');

function smarty_function_icon($params, &$smarty){
   if(isset($params['get'])){
		if(isset($params['notag'])){
			return Icon::get($params['get']);
		}else{
			return Icon::getTag($params['get']);
		}
	}
}


/* vim: set expandtab: */

?>
