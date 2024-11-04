<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbMaaltijd;

class NodeMaaltijd implements Node
{
	/**
	 * @psalm-return BbMaaltijd::class
	 */
	public static function getBbTagType(): string
	{
		return BbMaaltijd::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'maaltijd'
	 */
	public static function getNodeType()
	{
		return 'maaltijd';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!($node instanceof BbMaaltijd)) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'maaltijd' => $node->attrs->id,
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
