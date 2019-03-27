<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbNobold extends BbTag {
	public function getTagName() {
		return 'nobold';
	}

	public function parse($arguments = []) {
		$this->env->nobold = true;
		$return = $this->getContent();
		$this->env->nobold = false;

		return $return;
	}
}
