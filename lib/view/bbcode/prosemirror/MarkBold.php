<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbBold;
use CsrDelft\bb\tag\BbNode;

class MarkBold implements Mark
{
	public static function getBbTagType()
	{
		return BbBold::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public static function getMarkType()
	{
		return 'strong';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}
}
