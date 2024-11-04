<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPrive;

class MarkPrive implements Mark
{
	/**
	 * @psalm-return BbPrive::class
	 */
	public static function getBbTagType(): string
	{
		return BbPrive::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'prive'
	 */
	public static function getMarkType()
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
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['prive' => $node->getPermissie()],
		];
	}
}
