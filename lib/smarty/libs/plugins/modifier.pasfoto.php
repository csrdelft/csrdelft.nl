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
 * Name:     pasfoto<br>
 * Date:     August, 29 2007
 * Purpose:  process csrdelft.nl-uid to a pasfoto
 * Input:    uid to be converted to pasfoto
 * Example:  {$uid|pasfoto}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.1
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_pasfoto($uid, $cssclass = 'pasfoto', $link = true) {
	$uids = explode(',', $uid);
	$return = '';
	foreach ($uids as $uid) {
		$lid = LidCache::getLid($uid);
		if ($lid instanceof Lid) {
			if ($link) {
				$return.='<a href="/profiel/' . $uid . '" title="' . htmlspecialchars($lid->getNaamLink('volledig', 'plain')) . '">';
			}
			$return.=$lid->getPasfoto(true, $cssclass);
			if ($link) {
				$return.='</a>';
			}
		}
	}
	return $return;
}
