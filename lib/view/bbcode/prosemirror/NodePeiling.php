<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPeiling;

class NodePeiling implements Node
{
	/**
	 * @psalm-return BbPeiling::class
	 */
	public static function getBbTagType(): string
	{
		return BbPeiling::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'peiling'
	 */
	public static function getNodeType()
	{
		return 'peiling';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
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

	public function getTagAttributes($node)
	{
		return [
			'peiling' => $node->attrs->id,
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
