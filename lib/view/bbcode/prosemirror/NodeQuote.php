<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\bb\tag\BbQuote;

class NodeQuote implements Node
{
	/**
	 * @psalm-return BbQuote::class
	 */
	public static function getBbTagType(): string
	{
		return BbQuote::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'blockquote'
	 */
	public static function getNodeType()
	{
		return 'blockquote';
	}

	public function getData(BbNode $node)
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
