<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbSuperscript;

class MarkSuperscript implements Mark
{
	public static function getBbTagType(): string
	{
		return BbSuperscript::class;
	}

	public static function getMarkType(): string
	{
		return 'superscript';
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
