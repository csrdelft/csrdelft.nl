<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbVerticale;

class NodeVerticale implements Node
{
	/**
	 * @psalm-return BbVerticale::class
	 */
	public static function getBbTagType(): string
	{
		return BbVerticale::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'verticale'
	 */
	public static function getNodeType()
	{
		return 'verticale';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbVerticale) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getLetter()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'verticale' => $node->attrs->id,
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
