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
 * Name:     perm2string<br>
 * Date:     May, 27 2011
 * Purpose:  process csrdelft.nl-permission to a string(-link)
 * Input:    Permissions to be converted
 * Example:  {$permissie|perm2string}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Gerrit Uitslag < klapinklapin at gmail dot com>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_perm2string($permissies){
	return LoginLid::formatPermissionstring($permissies);
}
