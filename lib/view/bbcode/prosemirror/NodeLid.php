<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\bb\BbException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbLid;

class NodeLid implements Node
{
	/**
	 * @psalm-return BbLid::class
	 */
	public static function getBbTagType(): string
	{
		return BbLid::class;
	}

	/**
	 * @return string
	 *
	 * @psalm-return 'lid'
	 */
	public static function getNodeType()
	{
		return 'lid';
	}

	/**
	 * @return string[][]
	 *
	 * @psalm-return array{attrs: array{uid: string, naam: string}}
	 */
	public function getData(BbNode $node): array
	{
		if (!$node instanceof BbLid) {
			throw new \InvalidArgumentException();
		}

		try {
			$profiel = $node->getProfiel();

			return [
				'attrs' => [
					'uid' => $profiel->uid,
					'naam' => $profiel->getNaam('user'),
				],
			];
		} catch (BbException) {
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

	/**
	 * @return true
	 */
	public function selfClosing()
	{
		return true;
	}
}
