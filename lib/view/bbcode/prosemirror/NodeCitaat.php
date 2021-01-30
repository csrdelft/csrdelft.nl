<?php


namespace CsrDelft\view\bbcode\prosemirror;


use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCitaat;

class NodeCitaat implements Node
{
	public static function getBbTagType()
	{
		return BbCitaat::class;
	}

	public static function getNodeType()
	{
		return 'citaat';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbCitaat) {
			throw new \InvalidArgumentException();
		}

		if ($node->bron_profiel != null) {
			$van = $node->bron_profiel->getLink();
		} elseif ($node->bron_text != null) {
			$van = $node->bron_text;
		}

		return [
			'attrs' => [
				'van' => $van,
				'url' => $node->bron_url,
			]
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'citaat' => $node->attrs->van,
			'url' => $node->attrs->url
		];
	}

	public function selfClosing()
	{
		return false;
	}
}
