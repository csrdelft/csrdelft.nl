<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbNewline extends BbTag {

	public function getTagName() {
		return 'rn';
	}

	public function parse($arguments = []) {
		return '<br />';
	}
}
