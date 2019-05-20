<?php

namespace CsrDelft\view\bbcode\tag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOfftopic extends BbTag {

	public function getTagName() {
		return ['ot', 'offtopic', 'vanonderwerp'];
	}

	public function parse($arguments = []) {
		return '<span class="offtopic bb-tag-offtopic">' . $this->getContent() . '</span>';
	}
}
