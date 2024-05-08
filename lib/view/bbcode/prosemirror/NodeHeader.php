<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbHeading;
use CsrDelft\bb\tag\BbNode;

class NodeHeader implements Node
{
	public static function getBbTagType(): string
	{
		return BbHeading::class;
	}

	public static function getNodeType(): string
	{
		return 'heading';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbHeading) {
			throw new InvalidArgumentException();
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

	public function selfClosing(): bool
	{
		return false;
	}
}
