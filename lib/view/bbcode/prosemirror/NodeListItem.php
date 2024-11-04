<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbListItem;
use CsrDelft\bb\tag\BbNode;

class NodeListItem implements Node
{
	/**
	 * @psalm-return BbListItem::class
	 */
	public static function getBbTagType(): string
	{
		return BbListItem::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'list_item'
	 */
	public static function getNodeType()
	{
		return 'list_item';
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
