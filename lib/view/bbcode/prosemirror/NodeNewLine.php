<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNewline;
use CsrDelft\bb\tag\BbNode;

class NodeNewLine implements Node
{
	public static function getBbTagType(): string
	{
		return BbNewline::class;
	}

	public static function getNodeType(): string
	{
		return 'hard_break';
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
		return true;
	}
}
