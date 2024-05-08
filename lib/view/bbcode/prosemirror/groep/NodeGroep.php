<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbGroep;

class NodeGroep implements Node
{
	public static function getBbTagType(): string
	{
		return BbGroep::class;
	}

	public static function getNodeType(): string
	{
		return 'groep';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbGroep) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'groep' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
