<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Subscript
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [sub]Subscript[/sub]
 */
class BbSubscript extends BbTag {

	public function getTagName() {
		return 'sub';
	}

	public function parse($arguments = []) {
		return '<sub class="bb-tag-sub">' . $this->getContent(['sub', 'sup']) . '</sub>';
	}
}
