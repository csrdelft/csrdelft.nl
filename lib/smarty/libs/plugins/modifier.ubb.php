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
 * Name:     ubb<br>
 * Date:     August, 27 2007
 * Purpose:  process ubb-tags to html
 * Input:    string to be processed
 * Example:  {$var|ubb}
 * @link http://csrdelft.nl/feuten cat
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @return string
 */
function smarty_modifier_ubb($string){
   $ubb=new CsrUBB();
   return $ubb->getHTML($string);
}