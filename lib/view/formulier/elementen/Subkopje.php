<?php

namespace CsrDelft\view\formulier\elementen;

use CsrDelft\common\Util\ReflectionUtil;

/**
 * Subkopje.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class Subkopje extends HtmlComment
{
	public $h = 3;

	public function getHtml()
	{
		return '<h' .
			$this->h .
			' class="' .
			ReflectionUtil::classNameZonderNamespace(static::class) .
			'">' .
			$this->comment .
			'</h' .
			$this->h .
			'>';
	}
}
