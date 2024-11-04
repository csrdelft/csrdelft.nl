<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbHeading;
use CsrDelft\bb\tag\BbNode;

class NodeHeader implements Node
{
	/**
	 * @psalm-return BbHeading::class
	 */
	public static function getBbTagType(): string
	{
		return BbHeading::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'heading'
	 */
	public static function getNodeType()
	{
		return 'heading';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbHeading) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['level' => $node->getHeadingLevel()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'h' => $node->attrs->level,
		];
	}

	/**
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
