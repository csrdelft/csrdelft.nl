<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbActiviteit;

class NodeActiviteit implements Node
{
	/**
	 * @psalm-return BbActiviteit::class
	 */
	public static function getBbTagType(): string
	{
		return BbActiviteit::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'activiteit'
	 */
	public static function getNodeType()
	{
		return 'activiteit';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbActiviteit) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'activiteit' => $node->attrs->id,
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
