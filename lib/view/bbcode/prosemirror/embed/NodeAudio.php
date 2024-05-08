<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbAudio;

class NodeAudio implements Node
{
	public static function getBbTagType(): string
	{
		return BbAudio::class;
	}

	public static function getNodeType(): string
	{
		return 'audio';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbAudio) {
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
			'audio' => $node->attrs->url,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
