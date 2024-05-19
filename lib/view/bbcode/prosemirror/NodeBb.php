<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbBb;

class NodeBb implements Node
{
	public static function getBbTagType(): string
	{
		return BbBb::class;
	}

	public static function getNodeType(): string
	{
		return 'bb';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbBb) {
			throw new InvalidArgumentException();
		}
		$content = $node->getChildren();
		$node->setChildren([]);

		$contentString = implode(
			'',
			array_map(function (BbString $string) {
				return $string->getContent();
			}, $content)
		);

		return [
			'attrs' => ['bb' => str_replace('[br]', "\n", $contentString)],
		];
	}

	public function getTagAttributes($node)
	{
		return [$node->attrs->bb];
	}

	public function selfClosing(): bool
	{
		return false;
	}
}
