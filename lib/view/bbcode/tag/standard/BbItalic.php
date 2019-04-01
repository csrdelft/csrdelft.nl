<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbItalic extends BbTag {
	public function getTagName() {
		return 'i';
	}

	public function parse($arguments = []) {
		return '<em class="cursief bb-tag-i">' . $this->getContent(['i']) . '</em>';
	}
}
