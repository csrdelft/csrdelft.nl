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
 * Name:     filesize<br>
 * Date:     October 11, 2010
 * Purpose:  process file size in bytes to better human readable KB or MB size.
 * Input:    file size in bytes
 * Example:  {10234|filesize}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_filesize($size){
	$size=(int)$size;
	if($size> pow(1024, 2)){
		return round($size/pow(1024, 2), 1).' MB';
	}elseif($size>1024){
		return round($size/1024, 1) .' KB';
	}else{
		return $size.' B';
	}

}
