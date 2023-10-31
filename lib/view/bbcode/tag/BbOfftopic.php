<?php

namespace CsrDelft\view\bbcode\tag;

use CsrDelft\Lib\Bb\BbTag;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 27/03/2019
 */
class BbOfftopic extends BbTag
{
	public static function getTagName()
	{
		return ['ot', 'offtopic', 'vanonderwerp'];
	}

	public function render(): string
	{
		return '<span data-offtopic class="offtopic bb-tag-offtopic">' .
			$this->getContent() .
			'</span>';
	}

	/**
	 * @param array $arguments
	 */
	public function parse($arguments = []): void
	{
		$this->readContent();
	}
}
