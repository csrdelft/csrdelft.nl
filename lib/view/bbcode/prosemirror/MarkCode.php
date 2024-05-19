<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCodeInline;

class MarkCode implements Mark
{
	public static function getBbTagType(): string
	{
		return BbCodeInline::class;
	}

	public static function getMarkType(): string
	{
		return 'code';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}

	public function getData(BbNode $node)
	{
		return [];
	}
}
