<?php

/**
 * DokuWiki Plugin csrgroepgeschiedenis (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Gerrit Uitslag <klapinklapin@gmail.com>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
	die();

/**
 * Class syntax_plugin_csrgroepgeschiedenis
 */
class syntax_plugin_csrgroepgeschiedenis extends DokuWiki_Syntax_Plugin {

	/**
	 * Syntax Type
	 *
	 * @return string
	 */
	public function getType() {
		return 'substition';
	}

	/**
	 * Paragraph Type
	 *
	 * Defines how this syntax is handled regarding paragraphs. This is important
	 * for correct XHTML nesting. Should return one of the following:
	 *
	 * 'normal' - The plugin can be used inside paragraphs
	 * 'block'  - Open paragraphs need to be closed before plugin output
	 * 'stack'  - Special case. Plugin wraps other paragraphs.
	 *
	 * @see Doku_Handler_Block
	 * @return string
	 */
	public function getPType() {
		return 'block';
	}

	/**
	 * Sort for applying this mode
	 *
	 * @return int
	 */
	public function getSort() {
		return 155;
	}

	/**
	 * @param string $mode
	 */
	public function connectTo($mode) {
		$this->Lexer->addSpecialPattern('<csrgroepgeschiedenis.+?</csrgroepgeschiedenis>', $mode, 'plugin_csrgroepgeschiedenis');
	}

	/**
	 * Handler to prepare matched data for the rendering process
	 *
	 * @param   string       $match   The text matched by the patterns
	 * @param   int          $state   The lexer state for the match
	 * @param   int          $pos     The character position of the matched text
	 * @param   Doku_Handler $handler The Doku_Handler object
	 * @return  array Return an array with all data you want to use in render
	 */
	public function handle($match, $state, $pos, Doku_Handler $handler) {
		$match = substr($match, 21, -23);  // strip markup
		list($flags, $snaam) = explode('>', $match, 2);
		$flags = explode('&', substr($flags, 1));

		$geschiedenis = array(); //FIXME: OldGroep::getGroepgeschiedenis($snaam, 70);

		$data = array($flags, $geschiedenis);
		return $data;
	}

	/**
	 * Handles the actual output creation.
	 *
	 * @param   $mode   string        output format being rendered
	 * @param   $renderer Doku_Renderer the current renderer object
	 * @param   $data     array         data created by handler()
	 * @return  boolean                 rendered correctly?
	 */
	public function render($mode, Doku_Renderer $renderer, $data) {
		if ($mode != 'xhtml')
			return false;

		list(/* $flags */, $geschiedenis) = $data;

		// create a correctly nested list 
		$open = false;
		$lvl = 1;
		$renderer->listu_open();
		foreach ($geschiedenis as $groep) {
			if ($open)
				$renderer->listitem_close();
			$renderer->listitem_open($lvl);
			$open = true;

			$renderer->listcontent_open();
			$renderer->externallink($this->getConf('groepenurl') . $groep['type'] . '/' . $groep['id'], $groep['naam']);
			$renderer->listcontent_close();
		}
		$renderer->listitem_close();
		$renderer->listu_close();

		return true;
	}

}

// vim:ts=4:sw=4:et:
