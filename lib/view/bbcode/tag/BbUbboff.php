<?php

namespace CsrDelft\view\bbcode\tag;

/**
 * UBB off
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [ubboff]Not parsed[/ubboff]
 * @example [tekst]Not parsed[/tekst]
 */
class BbUbboff extends BbTag {

	public function getTagName() {
		return ['ubboff', 'tekst'];
	}

	public function parse($arguments = []) {
		$this->parser->bb_mode = false;
		$content = $this->getContent();
		$this->parser->bb_mode = true;
		return $content;
	}
}
