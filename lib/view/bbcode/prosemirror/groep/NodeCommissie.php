<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbCommissie;

class NodeCommissie implements Node
{
	public static function getBbTagType(): string
	{
		return BbCommissie::class;
	}

	public static function getNodeType(): string
	{
		return 'commissie';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbCommissie) {
			throw new InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'commissie' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
