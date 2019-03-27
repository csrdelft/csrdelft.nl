<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Table header cell
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [th]...[/th]
 */
class BbTableHeader extends BbTag {
	public function getTagName() {
		return 'th';
	}

	public function parse($arguments = []) {
		return '<th class="bb-tag-th">' . $this->getContent() . '</th>';
	}
}
