<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbMaaltijd;

class NodeMaaltijd implements Node
{
	public static function getBbTagType()
	{
		return BbMaaltijd::class;
	}

	public static function getNodeType()
	{
		return 'maaltijd';
	}

	public function getData(BbNode $node)
	{
		if (!($node instanceof BbMaaltijd)) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'maaltijd' => $node->attrs->id,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
