<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Slash me
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param optional String $arguments['me'] Name of who is me
 *
 * @example [me] waves
 * @example [me=Name] waves
 */
class BbMe extends BbTag {

	public function getTagName() {
		return 'me';
	}

	public function parse($arguments = []) {
		$content = $this->parser->parseArray(['[br]']);
		array_unshift($this->parser->parseArray, '[br]');
		if (isset($arguments['me'])) {
			return '<span style="color:red;">* ' . $arguments['me'] . $content . '</span>';
		} else {
			return '<span style="color:red;">/me' . $content . '</span>';
		}
	}
}
