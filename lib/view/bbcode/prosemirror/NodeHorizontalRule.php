<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbHorizontalRule;
use CsrDelft\bb\tag\BbNode;

class NodeHorizontalRule implements Node
{
	/**
	 * @psalm-return BbHorizontalRule::class
	 */
	public static function getBbTagType(): string
	{
		return BbHorizontalRule::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'horizontal_rule'
	 */
	public static function getNodeType()
	{
		return 'horizontal_rule';
	}

	/**
	 * @psalm-return array<never, never>
	 */
	public function getData(BbNode $node): array
	{
		return [];
	}

	/**
	 * @return array
	 *
	 * @psalm-return array<never, never>
	 */
	public function getTagAttributes($node)
	{
		return [];
	}

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
