<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Heading
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param Integer $arguments ['h'] Heading level (1-6)
 * @param string optional $arguments['id'] ID attribute
 *
 * @example [h=1 id=special]Heading[/h]
 */
class BbHeading extends BbTag {

	public function getTagName() {
		return 'h';
	}

	public function parse($arguments = []) {
		$id = '';
		if (isset($arguments['id'])) {
			$id = ' id="' . htmlspecialchars($arguments['id']) . '"';
		}
		$h = 1;
		if (isset($arguments['h'])) {
			$h = (int)$arguments['h'];
		}
		$content = $this->getContent(['h']);
		$text = "<h$h$id class=\"bb-tag-h\">$content</h$h>\n\n";

		// remove trailing br (or even two)
		$next_tag = array_shift($this->parser->parseArray);

		if ($next_tag != '[br]') {
			array_unshift($this->parser->parseArray, $next_tag);
		} else {
			$next_tag = array_shift($this->parser->parseArray);
			if ($next_tag != '[br]') {
				array_unshift($this->parser->parseArray, $next_tag);
			}
		}
		return $text;
	}

	public function isParagraphLess() {
		return true;
	}
}
