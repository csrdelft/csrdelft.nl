<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbCode;
use CsrDelft\Lib\Bb\Tag\BbNode;

class NodeCodeBlock implements Node
{
	public static function getBbTagType()
	{
		return BbCode::class;
	}

	public static function getNodeType()
	{
		return 'code_block';
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
		return false;
	}
}
