<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbStrikethrough;

class MarkStriketrough implements Mark
{
	/**
	 * @psalm-return BbStrikethrough::class
	 */
	public static function getBbTagType(): string
	{
		return BbStrikethrough::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'strikethrough'
	 */
	public static function getMarkType()
	{
		return 'strikethrough';
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

	/**
	 * @psalm-return array<never, never>
	 */
	public function getData(BbNode $node): array
	{
		return [];
	}
}
