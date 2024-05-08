<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbYoutube;

class NodeYoutube implements Node
{
	public static function getBbTagType(): string
	{
		return BbYoutube::class;
	}

	public static function getNodeType(): string
	{
		return 'youtube';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbYoutube) {
			throw new \InvalidArgumentException();
		}
		return [
			'attrs' => [
				'id' => $node->id,
			],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'youtube' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
