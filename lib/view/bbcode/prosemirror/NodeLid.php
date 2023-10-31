<?php

namespace CsrDelft\view\bbcode\prosemirror;

use CsrDelft\Lib\Bb\BbException;
use CsrDelft\Lib\Bb\Tag\BbNode;
use CsrDelft\view\bbcode\tag\BbLid;

class NodeLid implements Node
{
	public static function getBbTagType()
	{
		return BbLid::class;
	}

	public static function getNodeType()
	{
		return 'lid';
	}

	public function getData(BbNode $node)
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

	public function selfClosing()
	{
		return true;
	}
}
