<?php
/**
 * Templater Plugin: Based from the include plugin, like MediaWiki's template
 * Usage:
 * {{template>page}} for "page" in same namespace
 * {{template>:page}} for "page" in top namespace
 * {{template>namespace:page}} for "page" in namespace "namespace"
 * {{template>.namespace:page}} for "page" in subnamespace "namespace"
 * {{template>page#section}} for a section of "page"
 *
 * Replacers are handled in a simple key/value pair method.
 * {{template>page|key=val|key2=val|key3=val}}
 *
 * Templates are wiki pages, with replacers being delimited like
 * @key1@ @key2@ @key3@
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jonathan arkell <jonnay@jonnay.net> based on code by Esther Brunner <esther@kaffeehaus.ch> updated by Vincent de Lau <vincent@delau.nl> with bugfix from Ximin Luo <xl269@cam.ac.uk>
 * @version             0.3.1
 */

define('BEGIN_REPLACE_DELIMITER', '@');
define('END_REPLACE_DELIMITER', '@');

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_templater extends DokuWiki_Syntax_Plugin {
	/**
	 * return some info
	 */
	function getInfo() {
		return array(
			'author' => 'Jonathan Arkell (updated by Vincent de Lau)',
			'email'  => 'jonnay@jonnay.net',
			'date'   => '2009-03-21',
			'name'   => 'Templater Plugin',
			'desc'   => 'Displays a wiki page (or a section thereof) within another, with user selectable replacements',
			'url'    => 'http://www.dokuwiki.org/plugin:templater',
		);
	}

	/**

	 * What kind of syntax are we?
	 */
	function getType() {
		return 'container';
	}

	function getAllowedTypes() {
		return array('container', 'substition', 'protected', 'disabled', 'formatting');
	}

	/**
	 * Where to sort in?
	 */
	function getSort() {
		return 302;
	}

	/**
	 * Paragraph Type
	 */
	function getPType() {
		return 'block';
	}

	/**
	 * Connect pattern to lexer
	 */
	function connectTo($mode) {
		$this->Lexer->addSpecialPattern("{{template>.+?}}", $mode, 'plugin_templater');
	}

	/**
	 * Handle the match
	 */
	function handle($match, $state, $pos, &$handler) {
		global $ID;

		$match = substr($match, 11, -2);                        // strip markup
		$replacers = preg_split('/(?<!\\\\)\|/', $match);       // Get the replacers
		$wikipage = array_shift($replacers);

		$replacers = $this->_massageReplacers($replacers);

		$wikipage = preg_split('/\#/u', $wikipage, 2);                      // split hash from filename
		$parentpage = empty(self::$pagestack)? $ID : end(self::$pagestack); // get correct namespace
		resolve_pageid(getNS($parentpage), $wikipage[0], $exists);          // resolve shortcuts

		// check for perrmission
		if (auth_quickaclcheck($wikipage[0]) < 1)
			return false;

		return array($wikipage[0], $replacers, cleanID($wikipage[1]));
	}

	private static $pagestack = array(); // keep track of recursing template renderings

	/**
	 * Create output
	 * This is a refactoring candidate. Needs to be a little clearer.
	 */
	function render($mode, &$renderer, $data) {
		if ($mode != 'xhtml')
			return false;

		if ($data[0] === false) {
			// False means no permissions
			$renderer->doc .= '<div class="template"> No permissions to view the template </div>';
			$renderer->info['cache'] = FALSE;
			return true;
		}

		$file = wikiFN($data[0]);
		if (!@file_exists($file)) {
			$renderer->doc .= '<div class="templater">';
			$renderer->doc .= "Template {$data[0]} not found. ";
			$renderer->internalLink($data[0], '[Click here to create it]');
			$renderer->doc .= '</div>';
			$renderer->info['cache'] = FALSE;
			return true;
		} else if (array_search($data[0], self::$pagestack) !== false) {
			$renderer->doc .= '<div class="templater">';
			$renderer->doc .= "Processing of template {$data[0]} stopped due to recursion. ";
			$renderer->internalLink($data[0], '[Click here to edit it]');
			$renderer->doc .= '</div>';
			return true;
		}
		self::$pagestack[] = $data[0]; // push this onto the stack

		// Get the raw file, and parse it into its instructions. This could be cached... maybe.
		$rawFile = io_readfile($file);
		$rawFile = str_replace($data[1]['keys'], $data[1]['vals'], $rawFile);

		// replace unmatched substitutions with "" or use DEFAULT_STR from data arguments if exists.
		$left_overs = '/'.BEGIN_REPLACE_DELIMITER.'.*'.END_REPLACE_DELIMITER.'/';

		$def_key = array_search(BEGIN_REPLACE_DELIMITER."DEFAULT_STR".END_REPLACE_DELIMITER, $data[1]['keys']);
		$DEFAULT_STR = $def_key ? $data[1]['vals'][$def_key] : "";

		$rawFile = preg_replace($left_overs, $DEFAULT_STR, $rawFile);

		$instr = p_get_instructions($rawFile);

		// filter section if given
		if ($data[2])
			$instr = $this->_getSection($data[2], $instr);

		// correct relative internal links and media
		$instr = $this->_correctRelNS($instr, $data[0]);

		// render the instructructions on the fly
		$text = p_render('xhtml', $instr, $info);

		// remove toc, section edit buttons and category tags
		$patterns = array('!<div class="toc">.*?(</div>\n</div>)!s',
		                  '#<!-- SECTION \[(\d*-\d*)\] -->#e',
		                  '!<div class="category">.*?</div>!s');
		$replace  = array('', '', '');
		$text = preg_replace($patterns, $replace, $text);

		// prevent caching to ensure the included page is always fresh
		$renderer->info['cache'] = FALSE;

		// embed the included page
		$renderer->doc .= '<div class="templater">';
		$renderer->doc .= $text;
		$renderer->doc .= '</div>';

		array_pop(self::$pagestack); // pop off the stack when done
		return true;
	}

	/**
	 * Get a section including its subsections
	 */
	function _getSection($title, $instructions) {
		foreach ($instructions as $instruction) {
			if ($instruction[0] == 'header') {

				// found the right header
				if (cleanID($instruction[1][0]) == $title) {
					$level = $instruction[1][1];
					$i[] = $instruction;

				// next header of the same level -> exit
				} else if ($instruction[1][1] == $level)
					return $i;

			// add instructions from our section
			} else if (isset($level))
				$i[] = $instruction;
		}
		return $i;
	}

	/**
	 * Corrects relative internal links and media
	 */
	function _correctRelNS($instr, $incl) {
		global $ID;

		// check if included page is in same namespace
		$iNS = getNS($incl);
		if (getNS($ID) == $iNS)
			return $instr;

		// convert internal links and media from relative to absolute
		$n = count($instr);
		for($i = 0; $i < $n; $i++) {
			if (substr($instr[$i][0], 0, 8) != 'internal')
				continue;

			// relative subnamespace
			if ($instr[$i][1][0]{0} == '.') {
				$instr[$i][1][0] = $iNS.':'.substr($instr[$i][1][0], 1);

			// relative link
			} else if (strpos($instr[$i][1][0], ':') === false) {
				$instr[$i][1][0] = $iNS.':'.$instr[$i][1][0];
			}
		}

		return $instr;
	}

	/**
	 * Handles the replacement array
	 */
	function _massageReplacers($replacers) {
		$r = array();
		if (is_null($replacers)) {
			$r['keys'] = null;
			$r['vals'] = null;
		} else if (is_string($replacers)) {
			list($k, $v) = explode('=', $replacers, 2);
			$r['keys'] = BEGIN_REPLACE_DELIMITER.trim($k).END_REPLACE_DELIMITER;
			$r['vals'] = trim(str_replace('\|', '|', $v));
		} else if (is_array($replacers)) {
			foreach($replacers as $rep) {
				list($k, $v) = explode('=', $rep, 2);
				$r['keys'][] = BEGIN_REPLACE_DELIMITER.trim($k).END_REPLACE_DELIMITER;
				$r['vals'][] = trim(str_replace('\|', '|', $v));
			}
		} else {
			// This is an assertion failure. We should NEVER get here.
			//die("FATAL ERROR!  Unknown type passed to syntax_plugin_templater::massageReplaceMentArray() can't massage syntax_plugin_templater::\$replacers!  Type is:".gettype($r)." Value is:".$r);
			$r['keys'] = null;
			$r['vals'] = null;
		}
		return $r;
	}
}

//Setup VIM: ex: et ts=4 enc=utf-8 :

?>
