<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbClear extends BbTag {

	public function getTagName() {
		return 'clear';
	}

	public function parse($arguments = []) {
		$clearClass = 'clear';
		if (isset($arguments['clear']) && ($arguments['clear'] === 'left' || $arguments['clear'] === 'right')) {
			$clearClass .= '-' . $arguments['clear'];
		}
		return '<div class="' . $clearClass . '"></div>';
	}
}
