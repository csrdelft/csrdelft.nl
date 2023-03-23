<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOfftopic;

class MarkOfftopic implements Mark
{
	public static function getBbTagType()
	{
		return BbOfftopic::class;
	}

	public static function getMarkType()
	{
		return 'offtopic';
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
