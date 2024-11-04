<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbKetzer;

class NodeKetzer implements Node
{
	/**
	 * @psalm-return BbKetzer::class
	 */
	public static function getBbTagType(): string
	{
		return BbKetzer::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'ketzer'
	 */
	public static function getNodeType()
	{
		return 'ketzer';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbKetzer) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'ketzer' => $node->attrs->id,
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
