<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

class NodeString implements Node
{
	public static function getBbTagType(): string
	{
		return BbString::class;
	}

	public function getData(BbNode $node): array
	{
		return [
			'text' => $node->getContent(),
		];
	}

	public static function getNodeType(): string
	{
		return 'text';
	}

	public function getTagAttributes($node): array
	{
		return [];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
