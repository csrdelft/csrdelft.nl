<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOrderedList;

class NodeOrderedList implements Node
{
	public static function getBbTagType(): string
	{
		return BbOrderedList::class;
	}

	public static function getNodeType(): string
	{
		return 'ordered_list';
	}

	public function getData(BbNode $node): array
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

	public function getTagAttributes($node): array
	{
		return [
			'order' => $node->attrs->order,
		];
	}

	public function selfClosing(): bool
	{
		return false;
	}
}
