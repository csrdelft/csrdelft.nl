<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbBestuur;

class NodeBestuur implements Node
{
	/**
	 * @psalm-return BbBestuur::class
	 */
	public static function getBbTagType(): string
	{
		return BbBestuur::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'bestuur'
	 */
	public static function getNodeType()
	{
		return 'bestuur';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbBestuur) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'bestuur' => $node->attrs->id,
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
