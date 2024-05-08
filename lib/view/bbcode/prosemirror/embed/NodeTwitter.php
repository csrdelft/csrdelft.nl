<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbTwitter;

class NodeTwitter implements Node
{
	public static function getBbTagType(): string
	{
		return BbTwitter::class;
	}

	public static function getNodeType(): string
	{
		return 'twitter';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbTwitter) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'url' => $node->url,
			],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'twitter' => $node->attrs->url,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
