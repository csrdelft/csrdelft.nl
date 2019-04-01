<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Table row
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [tr]...
 * @example [tr]...[/tr]
 */
class BbTableRow extends BbTag {

	public function getTagName() {
		return 'tr';
	}

	public function parse($arguments = []) {
		return '<tr class="bb-tag-tr">' . $this->getContent(['br']) . '</tr>';
	}
}
