<?php


namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbHorizontalRule;
use CsrDelft\bb\tag\BbNode;

class NodeHorizontalRule implements Node
{
	public function getBbTagType()
	{
		return BbHorizontalRule::class;
	}

	public function getNodeType()
	{
		return 'horizontal_rule';
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'horizontal_rule',
		];
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
