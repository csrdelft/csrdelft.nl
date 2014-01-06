<?php
require_once 'icon.class.php';

/**
 * Smarty {icon} function plugin
 *
 * Type:     function
 * Name:     icon
 * Date:     Nov 4, 2009
 * Purpose:  Icon-generator
 * Input:
 * 
 * @package Smarty
 * @subpackage plugins
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
function smarty_function_icon($params, &$smarty){
   if(isset($params['get'])){
		if(isset($params['notag'])){
			return Icon::get($params['get'], $params['title']);
		}else{
			return Icon::getTag($params['get'], $params['title']);
		}
	}
}

?>