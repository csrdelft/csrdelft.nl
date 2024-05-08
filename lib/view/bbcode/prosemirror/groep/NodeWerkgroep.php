<?php

namespace CsrDelft\view\bbcode\prosemirror\groep;

use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\prosemirror\Node;
use CsrDelft\view\bbcode\tag\groep\BbWerkgroep;

class NodeWerkgroep implements Node
{
	public static function getBbTagType(): string
	{
		return BbWerkgroep::class;
	}

	public static function getNodeType(): string
	{
		return 'werkgroep';
	}

	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbWerkgroep) {
			throw new \InvalidArgumentException();
		}

		return [
			'attrs' => ['id' => $node->getId()],
		];
	}

	public function getTagAttributes($node): array
	{
		return [
			'werkgroep' => $node->attrs->id,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
