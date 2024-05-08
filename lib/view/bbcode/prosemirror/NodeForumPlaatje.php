<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbForumPlaatje;

class NodeForumPlaatje implements Node
{
	public static function getBbTagType(): string
	{
		return BbForumPlaatje::class;
	}

	public static function getNodeType(): string
	{
		return 'plaatje';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbForumPlaatje) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'key' => $node->getKey(),
				'src' => $node->getSourceUrl(),
			],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'plaatje' => $node->attrs->key,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
