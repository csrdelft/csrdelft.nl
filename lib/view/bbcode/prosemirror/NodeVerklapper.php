<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbVerklapper;

class NodeVerklapper implements Node
{
	public static function getBbTagType()
	{
		return BbVerklapper::class;
	}

	public function getData(BbNode $node)
	{
		return [];
	}

	public static function getNodeType()
	{
		return 'verklapper';
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
