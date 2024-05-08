<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbHorizontalRule;
use CsrDelft\bb\tag\BbNode;

class NodeHorizontalRule implements Node
{
	public static function getBbTagType(): string
	{
		return BbHorizontalRule::class;
	}

	public static function getNodeType(): string
	{
		return 'horizontal_rule';
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public function getTagAttributes($node)
	{
		return [];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
