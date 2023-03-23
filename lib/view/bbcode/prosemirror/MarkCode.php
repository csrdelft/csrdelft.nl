<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCodeInline;

class MarkCode implements Mark
{
	public static function getBbTagType()
	{
		return BbCodeInline::class;
	}

	public static function getMarkType()
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
