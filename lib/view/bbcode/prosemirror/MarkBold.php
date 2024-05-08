<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbBold;
use CsrDelft\bb\tag\BbNode;

class MarkBold implements Mark
{
	public static function getBbTagType(): string
	{
		return BbBold::class;
	}

	public function getData(BbNode $node): array
	{
		return [];
	}

	public static function getMarkType(): string
	{
		return 'strong';
	}

	public function getTagAttributes($mark): array
	{
		return [];
	}
}
