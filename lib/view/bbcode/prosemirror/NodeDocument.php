<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbDocument;

class NodeDocument implements Node
{

	public function getBbTagType()
	{
		return BbDocument::class;
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbDocument) {
			throw new \Exception();
		}

		return [
			'type' => 'document',
			'attrs' => [
				'id' => $node->id,
			],
		];
	}

	public function getNodeType()
	{
		return 'document'; // TODO: Not yet implemented in frontend
	}

	public function getTagAttributes($node)
	{
		return [
			'document' => $node->attrs->id,
		];
	}

	public function selfClosing()
	{
		return true;
	}
}
