<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbList;
use CsrDelft\bb\tag\BbNode;

class NodeBulletList implements Node
{
	public static function getBbTagType(): string
	{
		return BbList::class;
	}

	public static function getNodeType(): string
	{
		return 'bullet_list';
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
