<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbOndervereniging;

class NodeOndervereniging implements Node
{
	public static function getBbTagType(): string
	{
		return BbOndervereniging::class;
	}

	public static function getNodeType(): string
	{
		return 'ondervereniging';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbOndervereniging) {
			throw new InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'ondervereniging' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
