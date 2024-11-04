<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbYoutube;

class NodeYoutube implements Node
{
	/**
	 * @psalm-return BbYoutube::class
	 */
	public static function getBbTagType(): string
	{
		return BbYoutube::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'youtube'
	 */
	public static function getNodeType()
	{
		return 'youtube';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
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

	public function getTagAttributes($node)
	{
		return [
			'youtube' => $node->attrs->id,
		];
	}

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
