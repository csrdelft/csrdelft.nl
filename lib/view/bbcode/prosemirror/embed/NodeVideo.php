<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbVideo;

class NodeVideo implements Node
{
	public static function getBbTagType(): string
	{
		return BbVideo::class;
	}

	public static function getNodeType(): string
	{
		return 'video';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbVideo) {
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
			'video' => $node->attrs->url,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
