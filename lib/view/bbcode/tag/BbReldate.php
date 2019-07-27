<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\bb\BbTag;

/**
 * Relatieve datum zoals geparsed door php's strtotime
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [reldate]1 day ago[/reldate]
 * @example [reldate]20-01-2012[/reldate]
 * @example [reldate]20-01-2012 18:00[/reldate]
 */
class BbReldate extends BbTag {
	public function getTagName() {
		return 'reldate';
	}

	public function parse($arguments = []) {
		$content = $this->getContent();
		return '<span class="bb-tag-reldate" title="' . htmlspecialchars($content) . '">' . reldate($content) . '</span>';
	}
}
