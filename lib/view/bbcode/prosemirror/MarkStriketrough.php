<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbStrikethrough;

class MarkStriketrough implements Mark
{
	public static function getBbTagType(): string
	{
		return BbStrikethrough::class;
	}

	public static function getMarkType(): string
	{
		return 'strikethrough';
	}

	public function getTagAttributes($mark): array
	{
		return [];
	}

	public function getData(BbNode $node): array
	{
		return [];
	}
}
