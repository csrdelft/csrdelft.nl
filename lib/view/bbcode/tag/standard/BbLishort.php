<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * List item (short)
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [lishort]First item
 * @example [*]Next item
 */
class BbLishort extends BbTag {

	public function getTagName() {
		return ['lishort', '*'];
	}

	public function parse($arguments = []) {
		return '<li class="bb-tag-li">' . $this->parser->parseArray(['[br]']) . '</li>';
	}
}
