<?php

namespace CsrDelft\view\bbcode\prosemirror;

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

	public function getTagAttributes($mark): array
	{
		return [
			'prive' => $mark->attrs->prive,
		];
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbPrive) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['prive' => $node->getPermissie()],
		];
	}
}
