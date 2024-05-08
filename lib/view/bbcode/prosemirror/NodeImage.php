<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbImg;

class NodeImage implements Node
{
	public static function getBbTagType(): string
	{
		return BbImg::class;
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbImg) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'alt' => $node->getSourceUrl(),
				'src' => $node->getSourceUrl(),
				'title' => $node->getSourceUrl(),
			],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'img' => $node->attrs->src,
		];
	}

	public static function getNodeType(): string
	{
		return 'image';
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
