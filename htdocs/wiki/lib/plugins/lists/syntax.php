<?php
if (! class_exists('syntax_plugin_lists')) {
	if (! defined('DOKU_PLUGIN')) {
		if (! defined('DOKU_INC')) {
			define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
		} // if
		define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
	} // if
	// Include parent class:
	require_once(DOKU_PLUGIN . 'syntax.php');
	define('PLUGIN_LISTS', 'plugin_lists');

/**
 * <tt>syntax_plugin_lists.php </tt>- A PHP4 class that implements
 * a <tt>DokuWiki</tt> plugin for <tt>un/ordered lists</tt> block
 * elements.
 *
 * <p>
 * Usage:<br>
 * <tt>  * unordered item &lt;</tt>
 * <tt>  - ordered item &lt;</tt>
 * </p>
 * <pre>
 *	Copyright (C) 2005, 2007  DFG/M.Watermann, D-10247 Berlin, FRG
 *			All rights reserved
 *		EMail : &lt;support@mwat.de&gt;
 * </pre>
 * <div class="disclaimer">
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either
 * <a href="http://www.gnu.org/licenses/gpl.html">version 3</a> of the
 * License, or (at your option) any later version.<br>
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * </div>
 * @author <a href="mailto:support@mwat.de">Matthias Watermann</a>
 * @version <tt>$Id: syntax_plugin_lists.php,v 1.4 2007/08/15 12:36:19 matthias Exp $</tt>
 * @since created 29-Aug-2005
 */
class syntax_plugin_lists extends DokuWiki_Syntax_Plugin {

	/**
	 * @publicsection
	 */
	//@{

	/**
	 * Tell the parser whether the plugin accepts syntax mode
	 * <tt>$aMode</tt> within its own markup.
	 *
	 * @param $aMode String The requested syntaxmode.
	 * @return Boolean <tt>TRUE</tt> unless <tt>$aMode</tt> is
	 * <tt>PLUGIN_LISTS</tt> (which would result in a
	 * <tt>FALSE</tt> method result).
	 * @public
	 * @see getAllowedTypes()
	 */
	function accepts($aMode) {
		return (PLUGIN_LISTS != $aMode);
	} // accepts()

	/**
	 * Connect lookup pattern to lexer.
	 *
	 * @param $aMode String The desired rendermode.
	 * @public
	 * @see render()
	 */
	function connectTo($aMode) {
		if (PLUGIN_LISTS == $aMode) {
			return;
		} // if
		$this->Lexer->addEntryPattern(
			'\n\x20{2,}[\x2A\x2D]\s*(?=(?s).*?[^\x5C]\x3C\n\n)',
			$aMode, PLUGIN_LISTS);
		$this->Lexer->addPattern(
			'\n\x20{2,}[\x2A\x2D]\s*(?=(?s).*?[^\x5C]\x3C\n)', PLUGIN_LISTS);
		$this->Lexer->addEntryPattern(
			'\n\t+\s*[\x2A\x2D]\s*(?=(?s).*?[^\x5C]\x3C\n\n)',
			$aMode, PLUGIN_LISTS);
		$this->Lexer->addPattern(
			'\n\t+\s*[\x2A\x2D]\s*(?=(?s).*?[^\x5C]\x3C\n)', PLUGIN_LISTS);
	} // connectTo()

	/**
	 * Get an associative array with plugin info.
	 *
	 * <p>
	 * The returned array holds the following fields:
	 * <dl>
	 * <dt>author</dt><dd>Author of the plugin</dd>
	 * <dt>email</dt><dd>Email address to contact the author</dd>
	 * <dt>date</dt><dd>Last modified date of the plugin in
	 * <tt>YYYY-MM-DD</tt> format</dd>
	 * <dt>name</dt><dd>Name of the plugin</dd>
	 * <dt>desc</dt><dd>Short description of the plugin (Text only)</dd>
	 * <dt>url</dt><dd>Website with more information on the plugin
	 * (eg. syntax description)</dd>
	 * </dl>
	 * @return Array Information about this plugin class.
	 * @public
	 * @static
	 */
	function getInfo() {
		return array(
			'author' =>	'Matthias Watermann',
			'email' =>	'support@mwat.de',
			'date' =>	'2007-08-15',
			'name' =>	'List Syntax Plugin',
			'desc' =>	'Add HTML Style Un/Ordered Lists',
			'url' =>	'http://wiki.splitbrain.org/plugin:lists');
	} // getInfo()

	/**
	 * Define how this plugin is handled regarding paragraphs.
	 *
	 * <p>
	 * This method is important for correct XHTML nesting. It returns
	 * one of the following values:
	 * </p>
	 * <dl>
	 * <dt>normal</dt><dd>The plugin can be used inside paragraphs.</dd>
	 * <dt>block</dt><dd>Open paragraphs need to be closed before
	 * plugin output.</dd>
	 * <dt>stack</dt><dd>Special case: Plugin wraps other paragraphs.</dd>
	 * </dl>
	 * @return String <tt>'normal'</tt> .
	 * @public
	 * @static
	 */
	function getPType() {
		return 'normal';
	} // getPType()

	/**
	 * Where to sort in?
	 *
	 * @return Integer <tt>8</tt>, an arbitrary value smaller
	 * <tt>Doku_Parser_Mode_listblock</tt> (10).
	 * @public
	 * @static
	 */
	function getSort() {
		// class 'Doku_Parser_Mode_preformated' returns 20
		// class 'Doku_Parser_Mode_listblock' returns 10
		return 8;
	} // getSort()

	/**
	 * Get the type of syntax this plugin defines.
	 *
	 * @return String <tt>'container'</tt>.
	 * @public
	 * @static
	 */
	function getType() {
		return 'container';
	} // getType()

	/**
	 * Handler to prepare matched data for the rendering process.
	 *
	 * <p>
	 * The <tt>$aState</tt> parameter gives the type of pattern
	 * which triggered the call to this method:
	 * </p>
	 * <dl>
	 * <dt>DOKU_LEXER_ENTER</dt>
	 * <dd>a pattern set by <tt>addEntryPattern()</tt></dd>
	 * <dt>DOKU_LEXER_MATCHED</dt>
	 * <dd>a pattern set by <tt>addPattern()</tt></dd>
	 * <dt>DOKU_LEXER_EXIT</dt>
	 * <dd> a pattern set by <tt>addExitPattern()</tt></dd>
	 * <dt>DOKU_LEXER_SPECIAL</dt>
	 * <dd>a pattern set by <tt>addSpecialPattern()</tt></dd>
	 * <dt>DOKU_LEXER_UNMATCHED</dt>
	 * <dd>ordinary text encountered within the plugin's syntax mode
	 * which doesn't match any pattern.</dd>
	 * </dl>
	 * @param $aMatch String The text matched by the patterns.
	 * @param $aState Integer The lexer state for the match.
	 * @param $aPos Integer The character position of the matched text.
	 * @param $aHandler Object Reference to the Doku_Handler object.
	 * @return Array Index <tt>[0]</tt> holds the current
	 * <tt>$aState</tt>, index <tt>[1]</tt> the match prepared for
	 * the <tt>render()</tt> method.
	 * @public
	 * @see render()
	 * @static
	 */
	function handle($aMatch, $aState, $aPos, &$aHandler) {
		static $CHARS; static $ENTS;
		if (! is_array($CHARS)) {
			$CHARS = array('&','<', '>');
		} // if
		if (! is_array($ENTS)) {
			$ENTS = array('&#38;', '&#60;', '&#62;');
		} // if
		switch ($aState) {
			case DOKU_LEXER_ENTER:
				// fall through
			case DOKU_LEXER_MATCHED:
				$hits = array();
				if (preg_match('|\n*((\s*)(.))|', $aMatch, $hits)) {
					return array($aState, $hits[3],
						strlen(str_replace('  ', "\t", $hits[2])));
				} // if
				return array($aState, $aMatch);
			case DOKU_LEXER_UNMATCHED:
				$hits = array();
				if (preg_match('|^\s*\x3C$|', $aMatch, $hits)) {
					return array(DOKU_LEXER_UNMATCHED, '', +1);
				} // if
				if (preg_match('|(.*?)\s+\x3C$|s', $aMatch, $hits)) {
					return array(DOKU_LEXER_UNMATCHED,
						str_replace($CHARS, $ENTS,
							str_replace('\<', '<', $hits[1])), +1);
				} // if
				if (preg_match('|(.*[^\x5C])\x3C$|s', $aMatch, $hits)) {
					return array(DOKU_LEXER_UNMATCHED,
						str_replace($CHARS, $ENTS,
							str_replace('\<', '<', $hits[1])), +1);
				} // if
				return array(DOKU_LEXER_UNMATCHED,
					str_replace($CHARS, $ENTS,
						str_replace('\<', '<', $aMatch)), -1);
			case DOKU_LEXER_EXIT:
				// end of list
			default:
				return array($aState);
		} // switch
	} // handle()

	/**
	 * Add exit pattern to lexer.
	 *
	 * @public
	 */
	function postConnect() {
		// make sure the RegEx 'eats' only _one_ LF:
		$this->Lexer->addExitPattern('(?<=\x3C)\n(?=\n)', PLUGIN_LISTS);
	} // postConnect()

	/**
	 * Handle the actual output creation.
	 *
	 * <p>
	 * The method checks for the given <tt>$aFormat</tt> and returns
	 * <tt>FALSE</tt> when a format isn't supported. <tt>$aRenderer</tt>
	 * contains a reference to the renderer object which is currently
	 * handling the rendering. The contents of <tt>$aData</tt> is the
	 * return value of the <tt>handle()</tt> method.
	 * </p>
	 * @param $aFormat String The output format to generate.
	 * @param $aRenderer Object A reference to the renderer object.
	 * @param $aData Array The data created by the <tt>handle()</tt>
	 * method.
	 * @return Boolean <tt>TRUE</tt> if rendered successfully, or
	 * <tt>FALSE</tt> otherwise.
	 * @public
	 * @see handle()
	 */
	function render($aFormat, &$aRenderer, &$aData) {
		if ('xhtml' != $aFormat) {
			return FALSE;
		} // if
		static $LISTS = array('*' => 'ul', '-' => 'ol');
		static $LEVEL = 1;	// initial nesting level
		static $INLI = array();	// INLI[LEVEL] :: 0==open LI, 1==open LI/P
		static $CURRENT = array();	// CURRENT[LEVEL] :: * | -
		switch ($aData[0]) {
			case DOKU_LEXER_ENTER:
				$CURRENT[$LEVEL] = $aData[1];
				$hits = array();
				if (preg_match('|\s*<p>\s*$|i', $aRenderer->doc, $hits)) {
					$hits = -strlen($hits[0]);
					$aRenderer->doc = substr($aRenderer->doc, 0, $hits)
						. '<' . $LISTS[$aData[1]] . '>';
				} else {
					$aRenderer->doc .= '</p><' . $LISTS[$aData[1]] . '>';
				} // if
				// fall through to handle first item
			case DOKU_LEXER_MATCHED:
				// $aData[0] :: match state
				// $aData[1] :: * | -
				// $aData[2] :: nesting level
				$diff = $aData[2] - $LEVEL;
				if (0 < $diff) {	// going up one level
					$CURRENT[++$LEVEL] = $aData[1];
					$hits = array();
					if (preg_match('|</li>\s*$|', $aRenderer->doc)) {
						// need to open a new LI 
						$aRenderer->doc .= '<li class="level' . ($LEVEL - 1)
							. '"><' . $LISTS[$CURRENT[$LEVEL]] . '>';
						$INLI[$LEVEL - 1] = 0; // no closing P needed
					} else if (preg_match('|\s*<li[^>]*>\s*<p>\s*$|',
					$aRenderer->doc, $hits)) {
						// replace rudimentary LI
						$hits = -strlen($hits[0]);
						$aRenderer->doc = substr($aRenderer->doc, 0, $hits)
							. '<li class="level' . ($LEVEL - 1)
							. '"><' . $LISTS[$CURRENT[$LEVEL]] . '>';
						$INLI[$LEVEL - 1] = 0; // no closing P needed
					} else {	// possibly open LI
						if (isset($INLI[$LEVEL - 1])) {
							if (0 < $INLI[$LEVEL - 1]) {	// open LI P
								$aRenderer->doc .= '</p><'
									. $LISTS[$aData[1]] . '>';
								$INLI[$LEVEL - 1] = 0;
							} else {	// open LI
								$aRenderer->doc .= '<'
									. $LISTS[$aData[1]] . '>';
							} // if
						} else {	// no open LI
							$aRenderer->doc .= '<li class="level'
								. ($LEVEL - 1) . '"><'
								. $LISTS[$aData[1]] . '>';
							$INLI[$LEVEL - 1] = 0; // no closing P needed
						} // if
					} // if
				} else if (0 > $diff) {	// going back some levels
					do {
						--$LEVEL;
						$aRenderer->doc .= '</'
							. $LISTS[$CURRENT[$LEVEL + 1]] . '>';
						if (isset($INLI[$LEVEL])) {
							$aRenderer->doc .= (0 < $INLI[$LEVEL])
								? '</p></li>'
								: '</li>';
						} // if
					} while (0 > ++$diff);
				} else if ($aData[1] !=  $CURRENT[$LEVEL]) {
					// list type changed
					if (isset($INLI[$LEVEL])) {
						$aRenderer->doc .= (0 < $INLI[$LEVEL])
							? '</p></li>'
							: '</li>';
					} // if
					$aRenderer->doc .= '</' . $LISTS[$CURRENT[$LEVEL]]
						. '><' . $LISTS[$aData[1]] . '>';
					$CURRENT[$LEVEL] = $aData[1];
				} // if
				$aRenderer->doc .= '<li class="level' . $LEVEL . '"><p>';
				$INLI[$LEVEL] = 1;	// closing P needed
				return TRUE;
			case DOKU_LEXER_UNMATCHED:
				// $aData[0] :: match state
				// $aData[1] :: text
				// $aData[2] :: +1(EoT), -1(start/inbetween)
				if (0 < $aData[2]) {
					// last part of item's text
					if (strlen($aData[1])) {
						if (isset($INLI[$LEVEL])) {
							$aRenderer->doc .= (0 < $INLI[$LEVEL]) // LI P
								? $aData[1] . '</p></li>'
								: '<p>' . $aData[1] . '</p></li>';
						} else {	// no LI
							if (1 < $LEVEL) {	// assume a trailing LI text
								--$LEVEL;
								$aRenderer->doc .= '</'
									. $LISTS[$CURRENT[$LEVEL + 1]] . '><p>'
									. $aData[1] . '</p></li>';
							} else {
//XXX: There must be no data w/o context; the markup is broken. Whatever we
// could do it would be WRONG (and break XHMTL validity); hence comment:
								$aRenderer->doc .= '<!-- '. $aData[1] .' -->';
							} // if
						} // if
					} else {	// empty data
						$hits = array();
						if (preg_match('|\s*<li[^>]*>\s*<p>\s*$|',
						$aRenderer->doc, $hits)) {
							$hits = -strlen($hits[0]);
							// remove empty list item
							$aRenderer->doc = substr($aRenderer->doc, 0, $hits);
						} else if (preg_match('|\s*<p>\s*$|',
						$aRenderer->doc, $hits)) {
							$hits = -strlen($hits[0]);
							$aRenderer->doc =
								substr($aRenderer->doc, 0, $hits) . '</li>';
						} else if (isset($INLI[$LEVEL])) {
							$aRenderer->doc .= (0 < $INLI[$LEVEL])
								? '</p></li>'
								: '</li>';
						} // if
					} // if
					unset($INLI[$LEVEL]);
				} else {
					// item part between substitutions or nested blocks
					if (isset($INLI[$LEVEL])) {
						if (0 < $INLI[$LEVEL]) {	// LI P
							$aRenderer->doc .= $aData[1];
							$INLI[$LEVEL] = 1;
						} else {	// LI
							$aRenderer->doc .= '<p>' . $aData[1];
						} // if
					} else {	// data w/o context
						if (1 < $LEVEL) {	// assume a trailing LI text
							--$LEVEL;
							$aRenderer->doc .= '</' 
								. $LISTS[$CURRENT[$LEVEL + 1]] . '><p>'
								. $aData[1];
							$INLI[$LEVEL] = 1;
						} else {
							$aRenderer->doc .= $aData[1];
						} // if
					} // if
				} // if
				return TRUE;
			case DOKU_LEXER_EXIT:
				while (1 < $LEVEL) {
					--$LEVEL;
					$aRenderer->doc .= '</'. $LISTS[$CURRENT[$LEVEL + 1]] .'>';
					if (isset($INLI[$LEVEL])) {
						$aRenderer->doc .= (0 < $INLI[$LEVEL])
							? '</p></li>'
							: '</li>';
					} // if
				} // while
				// Since we have to use PType 'normal' we must open
				// a new paragraph for the following text
				$aRenderer->doc = preg_replace('|\s*<p>\s*</p>\s*|', '',
					$aRenderer->doc) . '</'. $LISTS[$CURRENT[$LEVEL]] .'><p>';
				$CURRENT = $INLI = array();
				$LEVEL = 1;
			default:
				return TRUE;
		} // switch
	} // render()

	//@}
} // class syntax_plugin_lists
} // if
?>
