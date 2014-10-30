<?php
/**
 * Render Plugin for XHTML output with preserved linebreaks
 *
 * @author Chris Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once DOKU_INC . 'inc/parser/xhtml.php';

/**
 * The Renderer
 */
class renderer_plugin_xhtmlniceurltitles extends Doku_Renderer_xhtml {

	function canRender($format) {
		return ($format=='xhtml');
	}

	/**
	 * Removes any Namespace from the given name but keeps
	 * casing and special chars
	 *
	 * [2014-10-09: copy of Doku_Renderer::_simpleTitle() , extended with sepchar replacement ]
	 *
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @author Gerrit Uitslag <klapinklapin@gmail.com>
	 */
	function _simpleTitle($name) {
		global $conf;

		$name = parent::_simpleTitle($name);

 		//replace the sepchar i.e. _, - or . by spaces in titles
 		return strtr($name, $conf['sepchar'], ' ');
	}

}

//Setup VIM: ex: et ts=4 enc=utf-8 :