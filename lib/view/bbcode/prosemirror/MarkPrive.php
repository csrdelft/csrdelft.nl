<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPrive;

class MarkPrive implements Mark
{
	public static function getBbTagType(): string
	{
		return BbPrive::class;
	}

	public static function getMarkType(): string
	{
		return 'prive';
	}

	public function getTagAttributes($mark)
	{
		return [
			'prive' => $mark->attrs->prive,
		];
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbPrive) {
			throw new InvalidArgumentException();
		}

		return [
			'attrs' => ['prive' => $node->getPermissie()],
		];
	}
}
