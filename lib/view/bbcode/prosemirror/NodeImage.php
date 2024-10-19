<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbImg;

class NodeImage implements Node
{
	public static function getBbTagType()
	{
		return BbImg::class;
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbImg) {
			throw new InvalidArgumentException();
		}

		return [
			'attrs' => [
				'alt' => $node->getSourceUrl(),
				'src' => $node->getSourceUrl(),
				'title' => $node->getSourceUrl(),
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'img' => $node->attrs->src,
		];
	}

	public static function getNodeType()
	{
		return 'image';
	}

	public function selfClosing()
	{
		return true;
	}
}
