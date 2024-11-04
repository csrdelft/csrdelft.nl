<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOfftopic;

class MarkOfftopic implements Mark
{
	/**
	 * @psalm-return BbOfftopic::class
	 */
	public static function getBbTagType(): string
	{
		return BbOfftopic::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'offtopic'
	 */
	public static function getMarkType()
	{
		return 'offtopic';
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
