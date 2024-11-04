<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbDocument;

class NodeDocument implements Node
{
	/**
	 * @psalm-return BbDocument::class
	 */
	public static function getBbTagType(): string
	{
		return BbDocument::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'document'
	 */
	public static function getNodeType()
	{
		return 'document';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbDocument) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'id' => $node->id,
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'document' => $node->attrs->id,
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
