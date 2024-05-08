<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbUrl;

class MarkLink implements Mark
{
	public static function getBbTagType(): string
	{
		return BbUrl::class;
	}

	public static function getMarkType(): string
	{
		return 'link';
	}

	public function getTagAttributes($mark): array
	{
		return [
			'url' => $mark->attrs->href,
		];
	}

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
