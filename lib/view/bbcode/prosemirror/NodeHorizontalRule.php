<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbHorizontalRule;
use CsrDelft\Lib\Bb\Tag\BbNode;

class NodeHorizontalRule implements Node
{
	public static function getBbTagType()
	{
		return BbHorizontalRule::class;
	}

	public static function getNodeType()
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

	public function selfClosing()
	{
		return true;
	}
}
