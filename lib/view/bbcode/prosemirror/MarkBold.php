<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbBold;
use CsrDelft\bb\tag\BbNode;

class MarkBold implements Mark
{
	public function getBbTagType()
	{
		return BbBold::class;
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'bold',
		];
	}
}
