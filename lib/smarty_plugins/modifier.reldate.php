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
 * Name:     reldate<br>
 * Date:     May 25, 2008
 * Purpose:  Maak een relative datum van een datum.
 * Input:    $datetime > datum van invoer
 * Example:  {2008-05-25 21:55:00|reldate}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_reldate($datetime) {
	return reldate($datetime);
}
