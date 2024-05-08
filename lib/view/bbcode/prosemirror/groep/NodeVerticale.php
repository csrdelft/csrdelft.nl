<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbVerticale;

class NodeVerticale implements Node
{
	public static function getBbTagType(): string
	{
		return BbVerticale::class;
	}

	public static function getNodeType(): string
	{
		return 'verticale';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbVerticale) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getLetter()],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'verticale' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
