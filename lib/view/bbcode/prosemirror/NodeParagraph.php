<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbParagraph;

class NodeParagraph implements Node
{
	public static function getBbTagType(): string
	{
		return BbParagraph::class;
	}

	public static function getNodeType(): string
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

	public function selfClosing(): bool
	{
		return false;
	}
}
