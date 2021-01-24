<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\BbTag;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\embed\BbVideo;

class NodeVideo implements Node
{
	public static function getBbTagType()
	{
		return BbVideo::class;
	}

	public static function getNodeType()
	{
		return 'video';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbVideo) {
			throw new \Exception();
		}

		return [
			'attrs' => [
				'url' => $node->url
			]
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'video' => $node->attrs->url
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
