<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mime-icon modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mimeicon<br>
 * Date:     October 26, 2009
 * Purpose:  Return a icon for a given mime-type.
 * Input:    mimetype
 * Example:  {$mimetype|mimeicon}
 * @link http://csrdelft.nl/feuten
 *          (svn repository)
 * @author   Jan Pieter Waagmeester < jpwaag at jpwaag dot com>
 * @version 1.0
 * @param string
 * @param string
 * @param bool
 * @return string
 */
function smarty_modifier_mimeicon($mimetype){
	if(		strpos($mimetype, 'image')){	return Icon::getTag('mime-image');
	}elseif(strpos($mimetype, 'msword')){	return Icon::getTag('mime-word');
	}elseif(strpos($mimetype, 'pdf')){		return Icon::getTag('mime-pdf');
	}elseif(strpos($mimetype, 'plain')){	return Icon::getTag('mime-text');
	}elseif(strpos($mimetype, 'zip')){		return Icon::getTag('mime-zip');
	}else{									return Icon::getTag('mime-onbekend');
	}
}
