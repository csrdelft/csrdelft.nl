<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\BbException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbLid;

class NodeLid implements Node
{
	public static function getBbTagType(): string
	{
		return BbLid::class;
	}

	public static function getNodeType(): string
	{
		return 'lid';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbLid) {
			throw new InvalidArgumentException();
		}

		try {
			$profiel = $node->getProfiel();

			return [
				'attrs' => [
					'uid' => $profiel->uid,
					'naam' => $profiel->getNaam('user'),
				],
			];
		} catch (BbException $exception) {
			return [
				'attrs' => [
					'uid' => $node->uid,
					'naam' => $node->uid,
				],
			];
		}
	}

	public function getTagAttributes($node)
	{
		return [
			'lid' => $node->attrs->uid,
		];
	}

	public function selfClosing(): bool
	{
		return true;
	}
}
