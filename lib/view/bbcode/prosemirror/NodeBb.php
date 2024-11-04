<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbBb;

class NodeBb implements Node
{
	/**
	 * @psalm-return BbBb::class
	 */
	public static function getBbTagType(): string
	{
		return BbBb::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'bb'
	 */
	public static function getNodeType()
	{
		return 'bb';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{bb: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbBb) {
			throw new \InvalidArgumentException();
		}
		$content = $node->getChildren();
		$node->setChildren([]);

		$contentString = implode(
			'',
			array_map(fn(BbString $string) => $string->getContent(), $content)
		);

		return [
			'attrs' => ['bb' => str_replace('[br]', "\n", $contentString)],
		];
	}

	public function getTagAttributes($node)
	{
		return [$node->attrs->bb];
	}

	/**
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
