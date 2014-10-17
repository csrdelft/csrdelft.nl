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
 * Name:     bbcode<br>
 * Date:     August, 27 2007
 * Purpose:  process bbcode-tags to html
 * Input:    string to be processed
 * Example:  {$var|bbcode}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_bbcode($string){
	return CsrBB::parse($string);
}
