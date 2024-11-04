<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbForumPlaatje;

class NodeForumPlaatje implements Node
{
	/**
	 * @psalm-return BbForumPlaatje::class
	 */
	public static function getBbTagType(): string
	{
		return BbForumPlaatje::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'plaatje'
	 */
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

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
