<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbVideo;

class NodeVideo implements Node
{
	public static function getBbTagType()
	{
		return BbVideo::class;
	}

	public static function getNodeType()
	{
		return 'video';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbVideo) {
			throw new InvalidArgumentException();
		}

		return [
			'attrs' => [
				'url' => $node->url,
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'video' => $node->attrs->url,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
