<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Quote
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [quote]Citaat[/quote]
 */
class BbQuote extends BbTag {
	public function getTagName() {
		return 'quote';
	}

	public function parse($arguments = []) {
		if ($this->env->quote_level == 0) {
			$this->env->quote_level = 1;
			$content = $this->getContent();
			$this->env->quote_level = 0;
		} else {
			$this->env->quote_level++;
			$delcontent = $this->getContent();
			$this->env->quote_level--;
			unset($delcontent);
			$content = '...';
		}

		return '<div class="citaatContainer bb-tag-quote"><strong>Citaat</strong>' .
			'<div class="citaat">' . $content . '</div></div>';
	}

	public function isParagraphLess() {
		return true;
	}
}
