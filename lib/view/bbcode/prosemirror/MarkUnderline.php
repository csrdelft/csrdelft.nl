<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\Lib\Bb\Tag\BbUnderline;

class MarkUnderline implements Mark
{
	public static function getBbTagType()
	{
		return BbUnderline::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public static function getMarkType()
	{
		return 'underline';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}
}
