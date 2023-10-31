<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNewline;
use CsrDelft\Lib\Bb\Tag\BbNode;

class NodeNewLine implements Node
{
	public static function getBbTagType()
	{
		return BbNewline::class;
	}

	public static function getNodeType()
	{
		return 'hard_break';
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
