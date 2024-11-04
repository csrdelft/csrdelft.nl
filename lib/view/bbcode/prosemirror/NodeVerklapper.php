<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbVerklapper;

class NodeVerklapper implements Node
{
	/**
	 * @psalm-return BbVerklapper::class
	 */
	public static function getBbTagType(): string
	{
		return BbVerklapper::class;
	}

	/**
	 * @psalm-return array<never, never>
	 */
	public function getData(BbNode $node): array
	{
		return [];
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'verklapper'
	 */
	public static function getNodeType()
	{
		return 'verklapper';
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
	 * @return false
	 */
	public function selfClosing()
	{
		return false;
	}
}
