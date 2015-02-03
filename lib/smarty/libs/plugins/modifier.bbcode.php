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
 * @author   P.W.G. Brussee < brussee at live dot nl>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_bbcode($bbcode, $mail = false) {
	if ($mail) {
		return CsrBB::parseMail($bbcode);
	}
	return CsrBB::parse($bbcode);
}
