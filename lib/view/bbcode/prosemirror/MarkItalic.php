<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbItalic;
use CsrDelft\bb\tag\BbNode;

class MarkItalic implements Mark
{
	/**
	 * @psalm-return BbItalic::class
	 */
	public static function getBbTagType(): string
	{
		return BbItalic::class;
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
	 * @psalm-return 'em'
	 */
	public static function getMarkType()
	{
		return 'em';
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
