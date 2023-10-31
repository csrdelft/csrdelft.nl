<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbWoonoord;

class NodeWoonoord implements Node
{
	public static function getBbTagType()
	{
		return BbWoonoord::class;
	}

	public static function getNodeType()
	{
		return 'woonoord';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbWoonoord) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'woonoord' => $node->attrs->id,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
