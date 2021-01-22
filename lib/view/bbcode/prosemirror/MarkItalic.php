<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbItalic;
use CsrDelft\bb\tag\BbNode;

class MarkItalic implements Mark
{
	public function getBbTagType()
	{
		return BbItalic::class;
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'italic',
		];
	}

	public function getMarkType()
	{
		return 'italic';
	}

	public function getTagAttributes($mark)
	{
		return [];
	}
}
