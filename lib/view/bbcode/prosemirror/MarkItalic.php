<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbItalic;
use CsrDelft\bb\tag\BbNode;

class MarkItalic implements Mark
{
	public static function getBbTagType(): string
	{
		return BbItalic::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public static function getMarkType(): string
	{
		return 'em';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}
}
