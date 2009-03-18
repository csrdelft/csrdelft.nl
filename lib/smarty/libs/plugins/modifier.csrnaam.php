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
 * Name:     csrnaam<br>
 * Date:     August, 29 2007
 * Purpose:  process csrdelft.nl-uid to a name-link
 * Input:    uid to be converted
 * Example:  {$uid|csrnaam}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_csrnaam($uid, $vorm='civitas', $mode='link'){
	$lid=LidCache::getLid($uid);
	return (string)$lid->getNaamLink($vorm, $mode);
}