<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\internal\BbString;
use CsrDelft\bb\tag\BbNode;

class NodeString implements Node
{
	/**
	 * @psalm-return BbString::class
	 */
	public static function getBbTagType(): string
	{
		return BbString::class;
	}

	/**
	 * @return void[]
	 *
	 * @psalm-return array{text: void}
	 */
	public function getData(BbNode $node): array
	{
		return [
			'text' => $node->getContent(),
		];
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'text'
	 */
	public static function getNodeType()
	{
		return 'text';
	}

	/**
	 * @return array
	 *
	 * @psalm-return array<never, never>
	 */
	public function getTagAttributes($node)
	{
		return [];
	}

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
