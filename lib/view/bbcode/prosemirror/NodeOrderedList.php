<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOrderedList;

class NodeOrderedList implements Node
{

	public static function getBbTagType()
	{
		return BbOrderedList::class;
	}

	public static function getNodeType()
	{
		return 'ordered_list';
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
