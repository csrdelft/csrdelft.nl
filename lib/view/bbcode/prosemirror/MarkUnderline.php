<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbUnderline;

class MarkUnderline implements Mark
{
	/**
	 * @psalm-return BbUnderline::class
	 */
	public static function getBbTagType(): string
	{
		return BbUnderline::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'underline'
	 */
	public static function getMarkType()
	{
		return 'underline';
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
}
