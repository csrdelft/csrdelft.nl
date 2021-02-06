<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbListItem;
use CsrDelft\bb\tag\BbNode;

class NodeListItem implements Node
{

	public static function getBbTagType()
	{
		return BbListItem::class;
	}

	public static function getNodeType()
	{
		return 'list_item';
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public function getTagAttributes($node)
	{
		return [];
	}

	public function selfClosing()
	{
		return false;
	}
}
