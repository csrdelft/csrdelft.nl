<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbSuperscript;

class MarkSuperscript implements Mark
{
	/**
	 * @psalm-return BbSuperscript::class
	 */
	public static function getBbTagType(): string
	{
		return BbSuperscript::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'superscript'
	 */
	public static function getMarkType()
	{
		return 'superscript';
	}

	/**
	 * @return array
	 *
	 * @psalm-return array<never, never>
	 */
	public function getTagAttributes($mark)
	{
		return [];
	}

	public function getData(BbNode $node)
	{
		return [];
	}
}
