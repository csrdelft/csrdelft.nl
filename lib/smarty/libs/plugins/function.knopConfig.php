<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {knopConfig} function plugin
 *
 * Type:     function
 * Name:     knopConfig
 * Date:     May 21, 2002
 * Purpose:  Configuratieding voor de knopjesgenerator
 * Input:
 *
 * Examples:
 * <pre>
	{knopConfig prefix=/foo/bar}
 * </pre>
 * @link http://feuten.csrdelft.nl
 * @version  1.2
 * @author   Jan Pieter Waagmeester <jpwaag@jpwaag.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */

//onthoudt een prefix voor de url van de knoppen die volgen.
function smarty_function_knopConfig($params, &$smarty){
	if(!isset($params['prefix'])){
        $smarty->trigger_error("knopConfig niet opgegeven");
        return;
    }
    $smarty->assign('knopUrlPrefix', $params['prefix']);
}


?>
