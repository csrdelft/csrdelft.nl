<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * List
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param optional String $arguments['list'] Type of ordered list
 *
 * @example [list]Unordered list[/list]
 * @example [ulist]Unordered list[/ulist]
 * @example [list=a]Ordered list numbered with lowercase letters[/list]
 */
class BbList extends BbTag {

	public function getTagName() {
		return ['list', 'ulist'];
	}

	public function parse($arguments = []) {
		if (!isset($arguments['list'])) {
			return '<ul class="bb-tag-list">' . $this->getContent(['br']) . '</ul>';
		} else {
			return '<ol class="bb-tag-list" type="' . $arguments['list'] . '">' . $this->getContent(['br']) . '</ol>';
		}
	}
}
