<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCodeInline;

class MarkCode implements Mark
{
	public function getBbTagType()
	{
		return BbCodeInline::class;
	}

	public function getMarkType()
	{
		return 'code';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'code',
		];
	}
}
