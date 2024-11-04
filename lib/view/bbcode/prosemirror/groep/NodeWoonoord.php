<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbWoonoord;

class NodeWoonoord implements Node
{
	/**
	 * @psalm-return BbWoonoord::class
	 */
	public static function getBbTagType(): string
	{
		return BbWoonoord::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'woonoord'
	 */
	public static function getNodeType()
	{
		return 'woonoord';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbWoonoord) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'woonoord' => $node->attrs->id,
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
