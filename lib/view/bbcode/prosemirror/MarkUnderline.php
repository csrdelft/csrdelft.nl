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

	/**
	 * @psalm-return array<never, never>
	 */
	public function getData(BbNode $node): array
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
