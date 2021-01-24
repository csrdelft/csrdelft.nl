<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\embed\BbSpotify;

class NodeSpotify implements Node
{
	public static function getBbTagType()
	{
		return BbSpotify::class;
	}

	public static function getNodeType()
	{
		return 'spotify';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbSpotify) {
			throw new \Exception();
		}

		return [
			'attrs' => [
				'url' => $node->uri,
				'formaat' => $node->formaat,
			]
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'spotify' => $node->attrs->url,
			'formaat' => $node->attrs->formaat,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
