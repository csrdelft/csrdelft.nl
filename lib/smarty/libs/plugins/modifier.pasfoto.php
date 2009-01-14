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
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_pasfoto($uid, $cssclass='pasfoto', $link=true){
	$lid=Lid::instance();
	if($lid->isValidUid($uid)){
		$return='';
		if($link){ $return.='<a href="/communicatie/profiel/'.$uid.'">'; }
		$return=$lid->getPasfoto($uid, true, $cssclass);
		if($link){ $return.='</a>'; }
		return $return;
	}else{
		return 'Ongeldige invoer';
	}

}