<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbBold;
use CsrDelft\bb\tag\BbNode;

class MarkBold implements Mark
{
	/**
	 * @psalm-return BbBold::class
	 */
	public static function getBbTagType(): string
	{
		return BbBold::class;
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
	 * @psalm-return 'strong'
	 */
	public static function getMarkType()
	{
		return 'strong';
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
