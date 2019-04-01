<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Superscript
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [sup]Superscript[/sup]
 */
class BbSuperscript extends BbTag {

	public function getTagName() {
		return 'sup';
	}

	public function parse($arguments = []) {
		return '<sup class="bb-tag-sup">' . $this->getContent(['sub', 'sup']) . '</sup>';
	}
}
