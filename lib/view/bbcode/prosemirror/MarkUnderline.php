<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbUnderline;

class MarkUnderline implements Mark
{
	public static function getBbTagType(): string
	{
		return BbUnderline::class;
	}

	public function getData(BbNode $node): array
	{
		return [];
	}

	public static function getMarkType(): string
	{
		return 'underline';
	}

	public function getTagAttributes($mark): array
	{
		return [];
	}
}
