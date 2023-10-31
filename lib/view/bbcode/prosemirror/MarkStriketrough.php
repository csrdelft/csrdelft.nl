<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\Lib\Bb\Tag\BbStrikethrough;

class MarkStriketrough implements Mark
{
	public static function getBbTagType()
	{
		return BbStrikethrough::class;
	}

	public static function getMarkType()
	{
		return 'strikethrough';
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
