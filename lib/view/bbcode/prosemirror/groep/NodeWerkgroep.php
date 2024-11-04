<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbWerkgroep;

class NodeWerkgroep implements Node
{
	/**
	 * @psalm-return BbWerkgroep::class
	 */
	public static function getBbTagType(): string
	{
		return BbWerkgroep::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'werkgroep'
	 */
	public static function getNodeType()
	{
		return 'werkgroep';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{id: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbWerkgroep) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'werkgroep' => $node->attrs->id,
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
