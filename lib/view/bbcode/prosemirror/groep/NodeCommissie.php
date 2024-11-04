<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbCommissie;

class NodeCommissie implements Node
{
	/**
	 * @psalm-return BbCommissie::class
	 */
	public static function getBbTagType(): string
	{
		return BbCommissie::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'commissie'
	 */
	public static function getNodeType()
	{
		return 'commissie';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbCommissie) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'commissie' => $node->attrs->id,
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
