<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {knop} function plugin
 *
 * Type:     function
 * Name:     knop
 * Date:     May 21, 2002
 * Purpose:  Knopjes generator zodat je dat niet 100 keer opniew intypt.
 * Input:
 *
 * Examples:
 * <pre>
 * {knop url="http://example.com" type=verwijderen}
 * </pre>
 * @link http://feuten.csrdelft.nl
 * @version  1.3
 * @author   Jan Pieter Waagmeester <jpwaag@jpwaag.com>
 *
 * @param    array
 * @param    Smarty
 *
 * @return   string
 */

use CsrDelft\view\Knop;

function smarty_function_knop($params, &$smarty) {
	if (!isset($params['url'])) {
		$smarty->trigger_error("knop: url niet opgegeven");
		return;
	}
	if (!isset($params['ignorePrefix'])) {
		$prefix = $smarty->getTemplateVars('knopUrlPrefix');
		if (isset($prefix)) {
			$params['url'] = $smarty->getTemplateVars('knopUrlPrefix') . $params['url'];
		}
	}
	$knop = new Knop($params['url']);

	if (isset($params['type'])) {
		$knop->setType($params['type']);
	}
	if (isset($params['text'])) {
		$knop->setText($params['text']);
	} elseif (isset($params['tekst'])) {
		$knop->setText($params['tekst']);
	}
	if (isset($params['title'])) {
		$knop->setTitle($params['title']);
	}
	if (isset($params['confirm'])) {
		$knop->setConfirm($params['confirm']);
	}
	if (isset($params['class'])) {
		$knop->setClass($params['class']);
	}

	return $knop->getHtml();
}


/* vim: set expandtab: */
