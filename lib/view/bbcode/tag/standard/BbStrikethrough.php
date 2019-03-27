<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbStrikethrough extends BbTag {

	public function getTagName() {
		return 's';
	}

	public function parse($arguments = []) {
		return '<del class="doorstreept bb-tag-s">' . $this->getContent(['s']) . '</del>';
	}
}
