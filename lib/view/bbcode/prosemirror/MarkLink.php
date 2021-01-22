<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbUrl;

class MarkLink implements Mark
{
	public function getBbTagType()
	{
		return BbUrl::class;
	}

	public function getMarkType()
	{
		return 'link';
	}

	public function getTagAttributes($mark)
	{
		return [
			'url' => $mark->attrs->href
		];
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbUrl) {
			throw new \Exception();
		}

		return [
			'href' => $node->url,
		];
	}
}
