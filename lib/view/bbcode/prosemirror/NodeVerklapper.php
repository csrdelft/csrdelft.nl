<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbVerklapper;

class NodeVerklapper implements Node
{
	public static function getBbTagType(): string
	{
		return BbVerklapper::class;
	}

	public function getData(BbNode $node): array
	{
		return [];
	}

	public static function getNodeType(): string
	{
		return 'verklapper';
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
