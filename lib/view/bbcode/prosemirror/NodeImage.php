<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbImg;

class NodeImage implements Node
{

	public function getBbTagType()
	{
		return BbImg::class;
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbImg) {
			throw new \Exception();
		}

		return [
			'type' => 'image',
			'attrs' => [
				'alt' => $node->getSourceUrl(),
				'src' => $node->getSourceUrl(),
				'title' => $node->getSourceUrl(),
			]
		];
	}
}
