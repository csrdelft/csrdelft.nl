<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Table cell
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param integer optional $arguments['w'] CSS width in pixels
 * @example [td w=50]...[/td]
 */
class BbTableCell extends BbTag {

	public function getTagName() {
		return 'td';
	}

	public function parse($arguments = []) {
		$style = '';
		if (isset($arguments['w'])) {
			$style .= 'width: ' . (int)$arguments['w'] . 'px; ';
		}

		return '<td class="bb-tag-td" style="' . $style . '">' . $this->getContent() . '</td>';
	}
}
