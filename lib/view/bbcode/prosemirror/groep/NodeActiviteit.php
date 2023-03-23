<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbActiviteit;

class NodeActiviteit implements Node
{
	public static function getBbTagType()
	{
		return BbActiviteit::class;
	}

	public static function getNodeType()
	{
		return 'activiteit';
	}

	public function getData(BbNode $node)
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

	public function selfClosing()
	{
		return true;
	}
}
