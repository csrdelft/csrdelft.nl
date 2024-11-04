<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCodeInline;

class MarkCode implements Mark
{
	/**
	 * @psalm-return BbCodeInline::class
	 */
	public static function getBbTagType(): string
	{
		return BbCodeInline::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'code'
	 */
	public static function getMarkType()
	{
		return 'code';
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
