<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOrderedList;

class NodeOrderedList implements Node
{
	/**
	 * @psalm-return BbOrderedList::class
	 */
	public static function getBbTagType(): string
	{
		return BbOrderedList::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'ordered_list'
	 */
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

	/**
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
