<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbGroep;

class NodeGroep implements Node
{
	/**
	 * @psalm-return BbGroep::class
	 */
	public static function getBbTagType(): string
	{
		return BbGroep::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'groep'
	 */
	public static function getNodeType()
	{
		return 'groep';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbGroep) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'groep' => $node->attrs->id,
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
