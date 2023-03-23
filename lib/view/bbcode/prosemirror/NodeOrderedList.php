<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
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
		if (!$node instanceof BbOrderedList) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'order' => $node->getOrder(),
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'order' => $node->attrs->order,
		];
	}

	public function selfClosing()
	{
		return false;
	}
}
