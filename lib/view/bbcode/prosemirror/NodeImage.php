<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbImg;

class NodeImage implements Node
{
	/**
	 * @psalm-return BbImg::class
	 */
	public static function getBbTagType(): string
	{
		return BbImg::class;
	}

	/**
	 * @return array[]
	 *
	 * @psalm-return array{attrs: array{alt: mixed, src: mixed, title: mixed}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbImg) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'alt' => $node->getSourceUrl(),
				'src' => $node->getSourceUrl(),
				'title' => $node->getSourceUrl(),
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'img' => $node->attrs->src,
		];
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'image'
	 */
	public static function getNodeType()
	{
		return 'image';
	}

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
