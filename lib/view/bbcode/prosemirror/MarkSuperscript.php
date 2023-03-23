<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\Lib\Bb\Tag\BbSuperscript;

class MarkSuperscript implements Mark
{
	public static function getBbTagType()
	{
		return BbSuperscript::class;
	}

	public static function getMarkType()
	{
		return 'superscript';
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
