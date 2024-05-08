<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbActiviteit;

class NodeActiviteit implements Node
{
	public static function getBbTagType(): string
	{
		return BbActiviteit::class;
	}

	public static function getNodeType(): string
	{
		return 'activiteit';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbActiviteit) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'activiteit' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
