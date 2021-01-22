<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

class NodeString implements Node
{
	public function getBbTagType()
	{
		return BbString::class;
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'text',
			'text' => $node->getContent(),
		];
	}

	public function getNodeType()
	{
		return 'text';
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
