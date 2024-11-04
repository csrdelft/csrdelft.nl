<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbParagraph;

class NodeParagraph implements Node
{
	/**
	 * @psalm-return BbParagraph::class
	 */
	public static function getBbTagType(): string
	{
		return BbParagraph::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'paragraph'
	 */
	public static function getNodeType()
	{
		return 'paragraph';
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
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
