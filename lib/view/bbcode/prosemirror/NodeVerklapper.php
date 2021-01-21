<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbVerklapper;

class NodeVerklapper implements Node
{
	public function getBbTagType()
	{
		return BbVerklapper::class;
	}

	public function getData(BbNode $node)
	{
		return [
			'type' => 'verklapper',
		];
	}
}
