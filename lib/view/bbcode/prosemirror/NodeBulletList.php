<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbList;
use CsrDelft\bb\tag\BbNode;

class NodeBulletList implements Node
{
	/**
	 * @psalm-return BbList::class
	 */
	public static function getBbTagType(): string
	{
		return BbList::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'bullet_list'
	 */
	public static function getNodeType()
	{
		return 'bullet_list';
	}

	/**
	 * @psalm-return array<never, never>
	 */
	public function getData(BbNode $node): array
	{
		return [];
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
