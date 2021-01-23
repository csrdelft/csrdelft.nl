<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbPrive;

class MarkPrive implements Mark
{
	public function getBbTagType()
	{
		return BbPrive::class;
	}

	public function getMarkType()
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
			throw new \Exception();
		}

		return [
			'type' => 'prive',
			'attrs' => ['prive' => $node->getPermissie()]
		];
	}
}
