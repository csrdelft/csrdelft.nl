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

	public function getNodeType()
	{
		return 'spoiler'; // TODO: Not yet implemented in frontend
	}

	public function getTagAttributes($node)
	{
		return [];
	}

	public function selfClosing()
	{
		return false;
	}
}
