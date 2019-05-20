<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbCommentaar extends BbTag {

	public function getTagName() {
		return 'commentaar';
	}

	public function parse($arguments = []) {
		$this->parser->bb_mode = false;
		$this->getContent();
		$this->parser->bb_mode = true;
		return '';
	}
}
