<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbParagraph;

class NodeParagraph implements Node
{
	public function getBbTagType()
	{
		return BbParagraph::class;
	}

	public function getNodeType()
	{
		return 'paragraph';
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'paragraph',
		];
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
