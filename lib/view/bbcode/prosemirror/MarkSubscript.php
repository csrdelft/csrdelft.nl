<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\Lib\Bb\Tag\BbSubscript;

class MarkSubscript implements Mark
{
	public static function getBbTagType()
	{
		return BbSubscript::class;
	}

	public static function getMarkType()
	{
		return 'subscript';
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
