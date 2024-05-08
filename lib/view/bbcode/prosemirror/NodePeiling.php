<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPeiling;

class NodePeiling implements Node
{
	public static function getBbTagType(): string
	{
		return BbPeiling::class;
	}

	public static function getNodeType(): string
	{
		return 'peiling';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbPeiling) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'id' => $node->getId(),
			],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'peiling' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
