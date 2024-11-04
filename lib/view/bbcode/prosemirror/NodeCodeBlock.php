<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbCode;
use CsrDelft\bb\tag\BbNode;

class NodeCodeBlock implements Node
{
	/**
	 * @psalm-return BbCode::class
	 */
	public static function getBbTagType(): string
	{
		return BbCode::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'code_block'
	 */
	public static function getNodeType()
	{
		return 'code_block';
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
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
