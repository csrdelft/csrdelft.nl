<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbCode;
use CsrDelft\bb\tag\BbNode;

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
