<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\tag\BbForumPlaatje;

class NodeForumPlaatje implements Node
{
	public static function getBbTagType()
	{
		return BbForumPlaatje::class;
	}

	public static function getNodeType()
	{
		return 'plaatje';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbForumPlaatje) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'key' => $node->getKey(),
				'src' => $node->getSourceUrl(),
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'plaatje' => $node->attrs->key,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
