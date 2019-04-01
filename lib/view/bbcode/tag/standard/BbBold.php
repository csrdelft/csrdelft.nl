<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbBold extends BbTag {
	public function getTagName() {
		return 'b';
	}

	public function parse($arguments = []) {
		if ($this->env->nobold === true && $this->env->quote_level == 0) {
			return $this->getContent(['b']);
		} else {
			return '<strong class="dikgedrukt bb-tag-b">' . $this->getContent(['b']) . '</strong>';
		}
	}
}
