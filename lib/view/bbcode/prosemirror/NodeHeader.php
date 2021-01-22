<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbHeading;
use CsrDelft\bb\tag\BbNode;

class NodeHeader implements Node
{
	public function getBbTagType()
	{
		return BbHeading::class;
	}

	public function getNodeType()
	{
		return 'heading';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbHeading) {
			throw new \Exception();
		}

		return [
			'attrs' => ['level' => $node->heading_level],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'h' => $node->attrs->level,
		];
	}

	public function selfClosing()
	{
		return false;
	}
}
