<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {knop} function plugin
 *
 * Type:     function<br>
 * Name:     knop<br>
 * Date:     May 21, 2002
 * Purpose:  Knopjes generator zodat je dat niet 100 keer opniew intypt.
 * Input:
 *
 * Examples:
 * <pre>
	{knop }
 * </pre>
 * @link http://feuten.csrdelft.nl
 * @version  1.2
 * @author   Jan Pieter Waagmeester <jpwaag@jpwaag.com>
 * @param    array
 * @param    Smarty
 * @return   string
 */
require_once('class.knop.php');

function smarty_function_knop($params, &$smarty){
    if(!isset($params['url'])){
        $smarty->trigger_error("knop: url niet opgegeven");
        return;
    }
    $knop=new Knop($params['url']);

    if(isset($params['type'])){
    	$knop->setType($params['type']);
    }
	if(isset($params['text'])){
		$knop->setText($params['text']);
	}elseif(isset($params['tekst'])){
		$knop->setText($params['tekst']);
	}
	if(isset($params['confirm'])){
		$knop->setConfirm($params['confirm']);
	}
	if(isset($params['class'])){
		$knop->setClass($params['class']);
	}

    return $knop->getHtml();
}


/* vim: set expandtab: */

?>
