<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbVideo;

class NodeVideo implements Node
{
	/**
	 * @psalm-return BbVideo::class
	 */
	public static function getBbTagType(): string
	{
		return BbVideo::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'video'
	 */
	public static function getNodeType()
	{
		return 'video';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbVideo) {
			throw new \InvalidArgumentException();
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

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
