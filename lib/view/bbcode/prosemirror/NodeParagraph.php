<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\tag\BbParagraph;

class NodeParagraph implements Node
{
	public static function getBbTagType()
	{
		return BbParagraph::class;
	}

	public static function getNodeType()
	{
		return 'paragraph';
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
