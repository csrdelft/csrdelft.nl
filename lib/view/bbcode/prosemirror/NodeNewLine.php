<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNewline;
use CsrDelft\bb\tag\BbNode;

class NodeNewLine implements Node
{
	/**
	 * @psalm-return BbNewline::class
	 */
	public static function getBbTagType(): string
	{
		return BbNewline::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'hard_break'
	 */
	public static function getNodeType()
	{
		return 'hard_break';
	}

	public function getData(BbNode $node)
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
