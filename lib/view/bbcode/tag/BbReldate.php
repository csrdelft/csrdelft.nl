<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;
use CsrDelft\common\Util\DateUtil;

/**
 * Relatieve datum zoals geparsed door php's strtotime
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 * @example [reldate]1 day ago[/reldate]
 * @example [reldate]20-01-2012[/reldate]
 * @example [reldate]20-01-2012 18:00[/reldate]
 */
class BbReldate extends BbTag
{
	public static function getTagName()
	{
		return 'reldate';
	}

	public function render(): string
	{
		return vsprintf("<span class=\"bb-tag-reldate\" title=\"%s\">%s</span>", [
			htmlspecialchars($this->getContent()),
			DateUtil::reldate($this->getContent()),
		]);
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []): void
	{
		$this->readContent([], false);
	}
}
