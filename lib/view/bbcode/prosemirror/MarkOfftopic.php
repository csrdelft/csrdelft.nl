<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbOfftopic;

class MarkOfftopic implements Mark
{
	public static function getBbTagType(): string
	{
		return BbOfftopic::class;
	}

	public static function getMarkType(): string
	{
		return 'offtopic';
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
