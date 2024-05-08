<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbKetzer;

class NodeKetzer implements Node
{
	public static function getBbTagType(): string
	{
		return BbKetzer::class;
	}

	public static function getNodeType(): string
	{
		return 'ketzer';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbKetzer) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'ketzer' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
