<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbKetzer;

class NodeKetzer implements Node
{
	public static function getBbTagType()
	{
		return BbKetzer::class;
	}

	public static function getNodeType()
	{
		return 'ketzer';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbKetzer) {
			throw new InvalidArgumentException();
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

	public function selfClosing()
	{
		return true;
	}
}
