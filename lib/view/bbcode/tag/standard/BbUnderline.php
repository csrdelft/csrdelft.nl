<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbUnderline extends BbTag{
	public function getTagName() {
		return 'u';
	}

	public function parse($arguments = []) {
		return '<ins class="onderstreept bb-tag-u">' . $this->getContent(['u']) . '</ins>';
	}
}
