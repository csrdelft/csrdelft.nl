<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Horizontal line
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [hr]
 */
class BbHorizontalRule extends BbTag {

	public function getTagName() {
		return 'hr';
	}

	public function parse($arguments = []) {
		return '<hr class="bb-tag-hr" />';
	}

	public function isParagraphLess() {
		return true;
	}
}
