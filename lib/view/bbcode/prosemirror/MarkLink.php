<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbUrl;

class MarkLink implements Mark
{
	/**
	 * @psalm-return BbUrl::class
	 */
	public static function getBbTagType(): string
	{
		return BbUrl::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'link'
	 */
	public static function getMarkType()
	{
		return 'link';
	}

	public function getTagAttributes($mark)
	{
		return [
			'url' => $mark->attrs->href,
		];
	}

	/**
	 * @return array[]
	 *
	 * @psalm-return array{attrs: array{href: mixed}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbUrl) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => [
				'href' => $node->url,
			],
		];
	}
}
