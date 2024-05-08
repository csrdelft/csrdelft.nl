<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbCode;
use CsrDelft\bb\tag\BbNode;

class NodeCodeBlock implements Node
{
	public static function getBbTagType(): string
	{
		return BbCode::class;
	}

	public static function getNodeType(): string
	{
		return 'code_block';
	}

	public function getData(BbNode $node): array
	{
		return [];
	}

	public function getTagAttributes($node): array
	{
		return [];
	}

	public function selfClosing(): bool
	{
		return false;
	}
}
