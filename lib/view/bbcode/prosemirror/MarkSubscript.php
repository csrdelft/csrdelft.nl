<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbSubscript;

class MarkSubscript implements Mark
{
	public static function getBbTagType(): string
	{
		return BbSubscript::class;
	}

	public static function getMarkType(): string
	{
		return 'subscript';
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
