<?php

namespace CsrDelft\view\bbcode\tag\standard;

use CsrDelft\view\bbcode\tag\BbTag;

/**
 * Code
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @param optional String $arguments['code'] Description of code type
 *
 * @example [code=PHP]phpinfo();[/code]
 */
class BbCode extends BbTag {

	public function getTagName() {
		return 'code';
	}

	public function parse($arguments = []) {
		$content = $this->getContent(['code', 'br', 'all' => 'all']);
		$code = isset($arguments['code']) ? $arguments['code'] . ' ' : '';

		return '<div class="bb-tag-code"><sub>' . $code . 'code:</sub><pre class="bbcode">' . $content . '</pre></div>';
	}
}
