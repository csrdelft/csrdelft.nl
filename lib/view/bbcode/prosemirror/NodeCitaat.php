<?php

namespace CsrDelft\view\bbcode\prosemirror;

use InvalidArgumentException;
use CsrDelft\bb\tag\BbNode;
use CsrDelft\view\bbcode\tag\BbCitaat;

class NodeCitaat implements Node
{
	public static function getBbTagType(): string
	{
		return BbCitaat::class;
	}

	public static function getNodeType(): string
	{
		return 'citaat';
	}

	public function getData(BbNode $node)
	{
		if (!$node instanceof BbCitaat) {
			throw new InvalidArgumentException();
		}

		if ($node->bron_profiel != null) {
			$van = $node->bron_profiel->uid;
			$naam = $node->bron_profiel->getNaam('user');
			$url = '/profiel/' . $node->bron_profiel->uid;
		} elseif ($node->bron_text != null) {
			$van = str_replace('_', ' ', $node->bron_text);
			$naam = $van;
			$url = $node->bron_url;
		} else {
			$van = '';
			$naam = '';
			$url = '';
		}

		return [
			'attrs' => [
				'van' => $van,
				'naam' => $naam,
				'url' => $url,
			],
		];
	}

	public function getTagAttributes($node)
	{
		return [
			'citaat' => str_replace(' ', '_', $node->attrs->van),
			'url' => $node->attrs->url,
		];
	}

	public function selfClosing(): bool
	{
		return false;
	}
}
