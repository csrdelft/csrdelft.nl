<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbHeading;
use CsrDelft\Lib\Bb\Tag\BbNode;

class NodeHeader implements Node
{
	public static function getBbTagType()
	{
		return BbHeading::class;
	}

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

	public function selfClosing()
	{
		return false;
	}
}
