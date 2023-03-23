<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbItalic;
use CsrDelft\Lib\Bb\Tag\BbNode;

class MarkItalic implements Mark
{
	public static function getBbTagType()
	{
		return BbItalic::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public static function getMarkType()
	{
		return 'em';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}
}
