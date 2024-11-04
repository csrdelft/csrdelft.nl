<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbSubscript;

class MarkSubscript implements Mark
{
	/**
	 * @psalm-return BbSubscript::class
	 */
	public static function getBbTagType(): string
	{
		return BbSubscript::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'subscript'
	 */
	public static function getMarkType()
	{
		return 'subscript';
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
