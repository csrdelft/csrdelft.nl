<?php

namespace CsrDelft\view\bbcode\prosemirror\embed;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\embed\BbTwitter;

class NodeTwitter implements Node
{
	/**
	 * @psalm-return BbTwitter::class
	 */
	public static function getBbTagType(): string
	{
		return BbTwitter::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'twitter'
	 */
	public static function getNodeType()
	{
		return 'twitter';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbTwitter) {
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
			'twitter' => $node->attrs->url,
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
