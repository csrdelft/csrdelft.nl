<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbDocument;

class NodeDocument implements Node
{
	public static function getBbTagType(): string
	{
		return BbDocument::class;
	}

	public static function getNodeType(): string
	{
		return 'document';
	}

	public function getData(BbNode $node): array
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

	public function getTagAttributes($node): array
	{
		return [
			'document' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
