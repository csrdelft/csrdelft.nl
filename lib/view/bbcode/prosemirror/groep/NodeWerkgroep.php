<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbWerkgroep;

class NodeWerkgroep implements Node
{
	public static function getBbTagType()
	{
		return BbWerkgroep::class;
	}

	public static function getNodeType()
	{
		return 'werkgroep';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbWerkgroep) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'werkgroep' => $node->attrs->id,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
