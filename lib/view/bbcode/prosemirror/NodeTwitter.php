<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\embed\BbTwitter;

class NodeTwitter implements Node
{
	public static function getBbTagType()
	{
		return BbTwitter::class;
	}

	public static function getNodeType()
	{
		return 'twitter';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbTwitter) {
			throw new \Exception();
		}

		return [
			'attrs' => [
				'url'=> $node->url
			]
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'twitter' => $node->attrs->url
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
